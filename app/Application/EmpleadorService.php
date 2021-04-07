<?php

namespace App\Application;

use App\Models\Empleador;
use App\Models\EmpleadorRepository;

class EmpleadorService {
  function buscarPorPatronal(string $numeroPatronal){
    $repo = new EmpleadorRepository();
    return $repo->buscarPorPatronal($numeroPatronal);
  }
}