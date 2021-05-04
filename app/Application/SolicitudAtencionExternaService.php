<?php

namespace App\Application;

use App\Http\Controllers\Controller;
use App\Models\Asegurado;
use App\Models\AseguradoRepository;
use App\Models\Empleador;
use App\Models\EmpleadorRepository;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador as GalenoEmpleador;
use App\Models\SolicitudAtencionExterna;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SolicitudAtencionExternaService extends Controller {

  protected function setQueryFilters($query, $filter){
    if(Arr::has($filter, "id")){
      $query->where("id", $filter["regional_id"]);
    }
    else{
      if(Arr::has($filter, "regional_id")){
        $query->where("regional_id", $filter["regional_id"]);
      }
      if(Arr::has($filter, "numero_patronal")){
        $empleador =GalenoEmpleador::buscarPorPatronal($filter["numero_patronal"]);
        // $asegurados_ids = [];
        // if($empleador){
        //   // $query->whereRaw(0);
        //   $aseguradoRepository = new AseguradoRepository();
        //   $asegurados = $aseguradoRepository->buscarPorEmpleador($empleador->id);
        //   if(Arr::has($filter, "matricula_asegurado")){
        //     $asegurados = $asegurados->where("matricula", $filter["matricula_asegurado"]);
        //   }
        //   $asegurados_ids = $asegurados->pluck("id");
        //   $this->asegurados = $asegurados;
        // }
        // $query->whereIn("asegurado_id", $asegurados_ids);
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

  public function registrar($regional_id, $asegurado_id, $medico_id, $proveedor_id, $prestaciones_solicitadas){
    $asegurado = Afiliado::buscarPorId($asegurado_id);
    $empleador = GalenoEmpleador::buscarPorId($asegurado->empleador_id);

    $hoy = Carbon::now("America/La_Paz");
    if($asegurado->estado == "Baja" && (!$asegurado->fecha_validez_seguro || $asegurado->fecha_validez_seguro->le($hoy))){
      throw ValidationException::withMessages([
        "asegurado.fecha_baja" => "El asegurado ha sido dado de baja hace mas de 2 meses"
      ]);
    }
    // if($asegurado->titular && $asegurado->titular->estado == "Baja" && $asegurado->titular->fecha_baja->addDays(60)->le($hoy)){
    //   throw ValidationException::withMessages([
    //     "asegurado.titular.fecha_baja" => "El titular del seguro ha sido dado de baja hace mas de 2 meses"
    //   ]);
    // }
    // if($empleador->estado == "Baja" && $empleador->fecha_baja->addDays(60)->le($hoy)){
    //   throw ValidationException::withMessages([
    //     "asegurado.empleador.fecha_baja" => "El empleador ha sido dado de baja hace mas de 2 meses"
    //   ]);
    // }
    // if($empleador->aportes == "En mora"){
    //   throw ValidationException::withMessages([
    //     "asegurado.empleador.aportes" => "El empleador esta en mora"
    //   ]);
    // }
    // if($asegurado->fecha_extinsion && $asegurado->fecha_extinsion->le($hoy)){
    //   throw ValidationException::withMessages([
    //     "asegurado.fecha_extinsion" => "La fecha de extinsion se ha cumplido"
    //   ]);
    // }
    
    $solicitud = new SolicitudAtencionExterna();

    $solicitud->fecha = $hoy;
    $solicitud->regional_id = $regional_id;
    $solicitud->asegurado_id = $asegurado_id;
    $solicitud->empleador_id = $asegurado->empleador->id;
    $solicitud->medico_id = $medico_id;
    $solicitud->proveedor_id = $proveedor_id;

    foreach($prestaciones_solicitadas as $prestacion_solicitada){
      $solicitud->prestacionesSolicitadas()->create($prestacion_solicitada, true);
    }
    DB::transaction(function() use($solicitud){
      $solicitud->save();
    });

    return $solicitud;
  }

  public function generarDatosParaFormularioDm11($numeroSolicitud){
    $solicitud = SolicitudAtencionExterna::with("prestacionesSolicitadas")->find($numeroSolicitud);
    Log::debug(json_encode($solicitud->toArray()));
    $asegurado = Afiliado::buscarPorId($solicitud->asegurado_id);
    $titular = $asegurado->afiliacionDelTitular ? Afiliado::buscarPorId($asegurado->afiliacionDelTitular->ID_AFO) : NULL;
    $empleador = GalenoEmpleador::buscarPorId($solicitud->empleador_id);

    return [
      "numero" => $numeroSolicitud,
      "fecha" => $solicitud->fecha,
      "regional" => $solicitud->regional->nombre,
      "proveedor" => $solicitud->proveedor->nombre,
      "titular" => !$titular ? [
        "matricula" => $asegurado->matricula,
        "nombre" => $asegurado->nombre_completo
        ] : [
          "matricula" => $titular->matricula,
          "nombre" => $titular->nombre_completo
        ],
      "beneficiario" => !$titular ? [
        "matricula" => ["","",""],
        "nombre" => ""
        ] : [
          "matricula" => $asegurado->matricula,
          "nombre" => $asegurado->nombre_completo
        ],
      "empleador" => $empleador->nombre,
      "doctor" => [
        "nombre" => $solicitud->medico->nombre_completo,
        "especialidad" => $solicitud->medico->especialidad
      ],
      "proveedor" => $solicitud->proveedor->nombre,
      "prestaciones" => $solicitud->prestacionesSolicitadas->map(function($prestacionSolicitada){
        Log::debug($prestacionSolicitada->toJson());
        return $prestacionSolicitada->prestacion . ($prestacionSolicitada->nota ? " - " . $prestacionSolicitada->nota : "");
      })->chunk(ceil($solicitud->prestacionesSolicitadas->count()/3))
    ];
  }

  public function actualizarUrlDm11($numeroSolicitud, $url){
    $solicitud = SolicitudAtencionExterna::find($numeroSolicitud);
    $solicitud->url_dm11 = $url;
    $solicitud->save();
    return $solicitud;
  }
}