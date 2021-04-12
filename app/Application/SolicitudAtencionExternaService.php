<?php

namespace App\Application;

use App\Http\Controllers\Controller;
use App\Models\AseguradoRepository;
use App\Models\EmpleadorRepository;
use App\Models\SolicitudAtencionExterna;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class SolicitudAtencionExternaService extends Controller {
  private $aseguradoRepository;

  protected function getAseguradoRepository(){
    return $this->aseguradoRepository ?: new AseguradoRepository();
  }

  protected function setQueryFilters($query, $filter){
    if(Arr::has($filter, "id")){
      $query->where("id", $filter["regional_id"]);
    }
    else{
      if(Arr::has($filter, "regional_id")){
        $query->where("regional_id", $filter["regional_id"]);
      }
      if(Arr::has($filter, "numero_patronal")){
        $empleadorRepository = new EmpleadorRepository();
        $empleador = $empleadorRepository->buscarPorPatronal($filter["numero_patronal"]);
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
        $aseguradoRepository = $this->getAseguradoRepository();
        $asegurado = $aseguradoRepository->buscarPorMatricula($filter["matricula_asegurado"]);
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
    $aseguradoRepository = $this->getAseguradoRepository();
    $asegurados = $aseguradoRepository->buscarPorIds($solicitudes->pluck("asegurado_id"));
        
    return $solicitudes->map(function($solicitud) use($asegurados){
      return [
        "numero" => $solicitud->numero,
        "fecha" => $solicitud->fecha,
        "asegurado" => $asegurados->where("id", $solicitud->asegurado_id)->first()->toArray(),
        "medico" => $solicitud->medico->nombreCompleto,
        "proveedor" => $solicitud->proveedor->nombre,
      ];
    });
  }

  public function generarDatosParaFormularioDm11($numeroSolicitud){
    $solicitud = SolicitudAtencionExterna::find($numeroSolicitud);
    $asegurado = $this->getAseguradoRepository()->buscarPorId($solicitud->asegurado_id);
    $empleador = (new EmpleadorRepository)->buscarPorId($solicitud->empleador_id);
    // var_dump($asegurado, $solicitud, $empleador);
    return [
      "numero" => $numeroSolicitud,
      "fecha" => $solicitud->fecha,
      "regional" => $solicitud->regional->nombre,
      "proveedor" => $solicitud->proveedor->nombre,
      "titular" => !$asegurado->titular ? [
        "matricula" => $asegurado->partes_matricula,
        "nombre" => $asegurado->nombre_completo
        ] : [
          "matricula" => $asegurado->titular->partes_matricula,
        "nombre" => $asegurado->titular->nombre
      ],
      "beneficiario" => !$asegurado->titular ? [
        "matricula" => ["","",""],
        "nombre" => ""
        ] : [
          "matricula" => $asegurado->partes_matricula,
        "nombre" => $asegurado->nombre_completo
      ],
      "empleador" => $empleador->nombre,
      "doctor" => [
        "nombre" => $solicitud->medico->nombre_completo,
        "especialidad" => $solicitud->medico->especialidad->nombre
      ],
      "proveedor" => $solicitud->proveedor->nombre,
      "prestaciones" => $solicitud->prestacionesSolicitadas->map(function($prestacion){
        return $prestacion->nombre;
      })->chunk(ceil($solicitud->prestacionesSolicitadas->count()/3))
    ];
  }

  public function actualizarUrlDm11($numeroSolicitud, $url){
    $solicitud = SolicitudAtencionExterna::find($numeroSolicitud);
    $solicitud->url_dm11 = $url;
    return $solicitud->save();
  }
}