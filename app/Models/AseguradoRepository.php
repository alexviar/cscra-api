<?php

namespace App\Models;

class AseguradoRepository {
  /** @var \Illuminate\Database\Eloquent\Collection */
  private $repo;

  function __construct()
  {
    // $empleador = new Empleador();
    $this->repo = collect([
      new Asegurado([
        "id" => 1,
        "matricula" => "11-0101-ABC-0",
        "apellido_paterno" => "Pellentesque",
        "apellido_materno" => "Dignissim",
        "nombres" => "Ligula"
      ]),
      new Asegurado([
        "id" => 2,
        "matricula" => "111-01002",
        "apellido_paterno" => "Ullamcorper",
        "apellido_materno" => "Tempor",
        "nombres" => "Nam"
      ])
    ]);
  }

  function buscarPorMatricula($matricula){
    return $this->repo->filter(function($asegurado) use($matricula){
      return \Illuminate\Support\Str::startsWith($asegurado->matricula, $matricula);
    })->first();
  }

  function buscarPorId($id){
    return $this->repo->where("id", $id)->first();
  }

  function buscarPorIds($ids){
    return $this->repo->whereIn("id", $ids);
  }
}