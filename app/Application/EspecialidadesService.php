<?php

namespace App\Application;

use App\Models\Especialidad;
use Illuminate\Support\Arr;

class EspecialidadesService {
  function buscar($filter, $pagination){
    $query = Especialidad::query();
    if(Arr::has($filter, "nombre") && $nombre=$filter["nombre"]){
      $query->where("nombre", "LIKE", "{$nombre}%");
    }
    $total = $query->count();
    $query->limit($pagination["size"])->offset(($pagination["current"]-1)*$pagination["size"]);
    return [$total, $query->get()];
  }

  function importar($filename, $separador, $formatoSaltoLinea){
    return Especialidad::importar($filename, $separador, $formatoSaltoLinea);
  }
}