<?php

namespace App\Application;

use App\Http\Controllers\Controller;
use App\Models\Asegurado;
use App\Models\AseguradoRepository;
use App\Models\Empleador;
use App\Models\EmpleadorRepository;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador as GalenoEmpleador;
use App\Models\Medico;
use App\Models\Prestacion;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SolicitudAtencionExternaService extends Controller {

  protected function setQueryFilters($query, $filter){
      if(Arr::has($filter, "regional_id")){
        $query->where("regional_id", $filter["regional_id"]);
      }
      if(Arr::has($filter, "registrado_por") && ($registradoPor = $filter["registrado_por"])){
        $query->where("usuario_id", $registradoPor);
      }
      if(Arr::has($filter, "numero_patronal")){
        $empleador = GalenoEmpleador::buscarPorPatronal($filter["numero_patronal"]);
        $query->where("empleador_id", $empleador->id);
      }
      if(Arr::has($filter, "matricula_asegurado")){
        $asegurado = Asegurado::buscarPorMatricula($filter["matricula_asegurado"]);
        $query->where("asegurado_id", $asegurado->id);
      }
      if(Arr::has($filter, "proveedor_id")){
        $query->where("proveedor_id", $filter["proveedor_id"]);
      }
      if(Arr::has($filter, "medico_id")){
        $query->where("medico_id", $filter["medico_id"]);
      }
      if(Arr::has($filter, "desde")){
        $query->whereDate("fecha", ">=", $filter["desde"]);
      }
      if(Arr::has($filter, "hasta")){
        $query->whereDate("fecha", "<=", $filter["hasta"]);
      }
    return $query;
  }

  function buscar(array $filter, array $page): array {
    $query = SolicitudAtencionExterna::query();

    $filteredQuery = $this->setQueryFilters($query, $filter);
    $total = $filteredQuery->count();

    $pageSize = 1;
    if(Arr::has($page, "size")){
      $pageSize = $page["size"];
      $filteredQuery->limit($pageSize);
    }
    if(Arr::has($page, "current")){
      $filteredQuery->offset(max($page["current"]-1, 0)*$pageSize);
    }

    return [$total, $this->prepareResult($filteredQuery)];
  }

  protected function prepareResult($query){
    $solicitudes = $query->with(["medico", "regional", "proveedor"])->get();
    $asegurados = Afiliado::buscarPorIds($solicitudes->pluck("asegurado_id"));

    return $solicitudes->map(function($solicitud) use($asegurados){
      return [
        "id" => $solicitud->id,
        "numero" => $solicitud->numero,
        "fecha" => $solicitud->fecha,
        "asegurado" => $asegurados->where("ID", $solicitud->asegurado_id)->first()->toArray(),
        "medico" => $solicitud->medico->nombreCompleto,
        "proveedor" => $solicitud->proveedor->medico?->nombreCompleto ?: $solicitud->proveedor->nombre,
        "url_dm11" => $solicitud->url_dm11
      ];
    });
  }

  public function registrar($regional_id, $asegurado_id, $medico_id, $proveedor_id, $usuario_id, $prestaciones_solicitadas){
    $asegurado = Afiliado::buscarPorId($asegurado_id);

    $hoy = Carbon::now("America/La_Paz");
    $errors = [];
    if(!$asegurado){
      $errors["asegurado.id"] = "El asegurado no existe";
    }
    else if(!$asegurado->ultimaAfiliacion){
      $errors["asegurado.id"] = "No se encontraron registros de la afiliacion";
    }
    else{
        if($asegurado->estado == 1){
            if($asegurado->ultimaAfiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
        }
        else if($asegurado->estado == 2){
            if(!$asegurado->ultimaAfiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja.";
        }
        else {
            $errors["asegurado.estado"] = "El asegurado tiene un estado indeterminado";
        }

        if($asegurado->ultimaAfiliacion->baja){
            if(!$asegurado->fechaValidezSeguro) $errors["asegurado.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
            else if($asegurado->fechaValidezSeguro->le($hoy)) $errors["asegurado.fecha_validez_seguro"] = "El seguro a no tiene validez";
        }
        if($asegurado->fechaExtincion?->le($hoy)){
            $errors["asegurado.fecha_extincion"] = "Fecha de extincion alcanzada";
        }

        if($asegurado->tipo == 2){
            $titular = $asegurado->titular;
            if($titular->estado == 1){
                if($asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
            }
            else if($titular->estado == 2){
                if(!$asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja.";
            }
            else {
                $errors["titular.estado"] = "El asegurado tiene un estado indeterminado";
            }

            if($asegurado->afiliacionDelTitular->baja){
                if(!$asegurado->afiliacionDelTitular->baja->fechaValidezSeguro) $errors["titular.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                else if($asegurado->afiliacionDelTitular->baja->fechaValidezSeguro->le($hoy)) $errors["titular.fecha_validez_seguro"] = "El seguro a no tiene validez";
            }
        }

        $empleador = $asegurado->empleador;
        if($empleador->estado == 1){
          if($empleador->fechaBaja) $errors["empleador.estado"] = "El empleador figura como activo, pero tiene una fecha de baja";
        }
        else if($empleador->estado == 2 || $empleador->estado == 3){
          if($empleador->fechaBaja) $error["empleador.fecha_baja"] = "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez";
        }
        else {
          $errors["titular.estado"] = "El empleador tiene un estado indeterminado";
        }
    }

    $medico = Medico::find($medico_id);
    if(!$medico){
      $errors["medico"] = "El medico no existe";
    }
    $proveedor = Proveedor::find($proveedor_id);
    if(!$proveedor){
      $errors["proveedor"] = "El proveedor no existe";
    }
    if(count($prestaciones_solicitadas) == 0){
      $errors["prestaciones_solicitadas"] = "No se solicitaron prestaciones";
    }
    if(count($prestaciones_solicitadas) > 1){
      $errors["prestaciones_solicitadas"] = "Actualmente solo se permite una prestacion por DM 11";
    }
    // $length = 0;
    foreach($prestaciones_solicitadas as $index => ["prestacion_id" => $prestacion_id, "nota" => $nota]){
      $prestacion = Prestacion::find($prestacion_id);
      if(!$prestacion){
        $errors["prestaciones_solicitadas.$index.prestacion"] = "La prestación no existe";
      }
      else if(!$proveedor->ofrece($prestacion_id)){
        $errors["prestaciones_solicitadas.$index.prestacion"] = "El proveedor no ofrece la prestacion '{$prestacion->nombre}'";
      }
      if(strlen($nota) > 150){
        $errors["prestaciones_solicitadas.$index.nota"] = "Las notas no deben exceder los 150 caracteres";
      }
      // $length += strlen($prestacion->nombre) + strlen($nota) + 3;
    }
    if(count($errors)){
      throw ValidationException::withMessages($errors);
    }

    $solicitud = new SolicitudAtencionExterna();

    $solicitud->fecha = $hoy;
    $solicitud->regional_id = $regional_id;
    $solicitud->asegurado_id = $asegurado_id;
    // $solicitud->titular_id = $asegurado->titular->id;
    $solicitud->empleador_id = $asegurado->empleador->id;
    $solicitud->medico_id = $medico_id;
    $solicitud->proveedor_id = $proveedor_id;
    $solicitud->usuario_id = $usuario_id;

    foreach($prestaciones_solicitadas as $prestacion_solicitada){
      $solicitud->prestacionesSolicitadas()->create($prestacion_solicitada, true);
    }
    DB::transaction(function() use($solicitud){
      $solicitud->save();
      $solicitud->url_dm11 = route("forms.dm11", [
        "numero" => $solicitud->numero
      ]);
      $solicitud->save();
    });

    return $solicitud;
  }

}
