<?php

namespace App\Application;

use App\Http\Controllers\Controller;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador as GalenoEmpleador;
use App\Models\ListaMoraItem;
use App\Models\Medico;
use App\Models\Prestacion;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SolicitudAtencionExternaService extends Controller
{

    protected function setQueryFilters($query, $filter)
    {
        if(($numero = Arr::get($filter, "numero")) && is_numeric($numero)) {
            $query->where("id", $numero);
        }
        else {
            if (($numeroPatronal = Arr::get($filter, "numero_patronal"))) {
                $empleador = GalenoEmpleador::buscarPorPatronal($numeroPatronal);
                $query->where("empleador_id", $empleador->id ?? 0);
            }
            if (($matricula = Arr::get($filter, "matricula_asegurado"))) {
                $asegurados = Afiliado::buscarPorMatricula($matricula);
                $query->whereIn("asegurado_id", $asegurados->pluck("ID"));
            }
            if (($regionalId = Arr::get($filter, "regional_id"))) {
                $query->where("regional_id", $regionalId);
            }
            if (($registradoPor = Arr::get($filter, "registrado_por_id"))) {
                $query->where("usuario_id", $registradoPor);
            }
            if (($proveedorId = Arr::get($filter, "proveedor_id"))) {
                $query->where("proveedor_id", $proveedorId);
            }
            if (($medicoId = Arr::get($filter, "medico_id"))) {
                $query->where("medico_id", $medicoId);
            }
            if (($desde = Arr::get($filter, "desde"))) {
                $query->where("fecha", ">=", $desde);
            }
            if (($hasta = Arr::get($filter, "hasta"))) {
                $query->whereDate("fecha", "<=", $hasta);
            }
        }
        return $query;
    }

    function buscar(array $filter, array $page): array
    {
        $query = SolicitudAtencionExterna::query();

        $filteredQuery = $this->setQueryFilters($query, $filter);
        $total = $filteredQuery->count();

        $pageSize = 1;
        if (Arr::has($page, "size")) {
            $pageSize = $page["size"];
            $filteredQuery->limit($pageSize);
        }
        if (Arr::has($page, "current")) {
            $filteredQuery->offset(max($page["current"] - 1, 0) * $pageSize);
        }

        return [$total, $this->prepareResult($filteredQuery)];
    }

    protected function prepareResult($query)
    {
        $solicitudes = $query->with(["medico", "regional", "proveedor"])->get();
        $asegurados = Afiliado::buscarPorIds($solicitudes->pluck("asegurado_id"));

        return $solicitudes->map(function ($solicitud) use ($asegurados) {
            return [
                "id" => $solicitud->id,
                "numero" => $solicitud->numero,
                "fecha" => $solicitud->fecha,
                "asegurado" => $asegurados->where("ID", $solicitud->asegurado_id)->first()->toArray(),
                "medico" => $solicitud->medico->nombreCompleto,
                "proveedor" => $solicitud->proveedor->nombre ?? $solicitud->proveedor->nombreCompleto,
                "url_dm11" => $solicitud->url_dm11
            ];
        });
    }

    public function registrar($regional_id, $asegurado_id, $medico_id, $proveedor_id, $usuario_id, $prestaciones_solicitadas)
    {
        $asegurado = Afiliado::buscarPorId($asegurado_id);
        $hoy = Carbon::now("America/La_Paz");
        $errors = [];
        if (!$asegurado) {
            $errors["asegurado.id"] = "El asegurado no existe";
        } else if (!$asegurado->ultimaAfiliacion) {
            $errors["asegurado.id"] = "No se encontraron registros de la afiliacion";
        } else {
            if ($asegurado->estado == 1) {
                if ($asegurado->ultimaAfiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
            } else if ($asegurado->estado == 2) {
                if (!$asegurado->ultimaAfiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja";
            } else {
                $errors["asegurado.estado"] = "El asegurado tiene un estado indeterminado";
            }

            if ($asegurado->ultimaAfiliacion->baja) {
                if (!$asegurado->fechaValidezSeguro) $errors["asegurado.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                else if ($asegurado->fechaValidezSeguro->lte($hoy)) $errors["asegurado.fecha_validez_seguro"] = "El seguro ya no tiene validez";
            }
            if ($asegurado->fechaExtincion && $asegurado->fechaExtincion->lte($hoy)) {
                $errors["asegurado.fecha_extincion"] = "Fecha de extincion alcanzada";
            }

            if ($asegurado->tipo == 2 && $asegurado->afiliacion->parentesco != 8) {
                $titular = $asegurado->titular;
                if ($titular->estado == 1) {
                    if ($asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
                } else if ($titular->estado == 2) {
                    if (!$asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja";
                } else {
                    $errors["titular.estado"] = "El asegurado tiene un estado indeterminado";
                }

                if ($asegurado->afiliacionDelTitular->baja) {
                    if (!$asegurado->afiliacionDelTitular->baja->fechaValidezSeguro) $errors["titular.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                    else if ($asegurado->afiliacionDelTitular->baja->fechaValidezSeguro->lte($hoy)) $errors["titular.fecha_validez_seguro"] = "El seguro ya no tiene validez";
                }
            }

            $empleador = $asegurado->empleador;
            if ($empleador->estado == 1) {
                if ($empleador->fecha_baja) $errors["empleador.estado"] = "El empleador figura como activo, pero tiene una fecha de baja";
            } else if ($empleador->estado == 2 || $empleador->estado == 3) {
                if (!$empleador->fecha_baja) $errors["empleador.fecha_baja"] = "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez";
                else if ($empleador->fecha_baja->addMonths(2)->lte($hoy)) $errors["empleador.fecha_baja"] = "El seguro ya no tiene validez";
            } else {
                $errors["empleador.estado"] = "El empleador tiene un estado indeterminado";
            }
            if(ListaMoraItem::where("empleador_id", $empleador->id)->exists()){
                $errors["empleador.aportes"] = "El empleador esta en mora";
            }
        }

        $medico = Medico::find($medico_id);
        if (!$medico) {
            $errors["medico"] = "El médico no existe";
        } else if ($medico->regional_id !== $regional_id) {
            $errors["medico"] = "El médico pertenece a otra regional";
        }

        $proveedor = Proveedor::find($proveedor_id);
        if (!$proveedor) {
            $errors["proveedor"] = "El proveedor no existe";
        } else if ($proveedor->regional_id !== $regional_id) {
            $errors["proveedor"] = "El proveedor pertenece a otra regional";
        } else if (!$proveedor->contrato) {
            $errors["proveedor"] = "El proveedor no tiene un contrato activo";
        } else {
            if (count($prestaciones_solicitadas) == 0) {
                $errors["prestaciones_solicitadas"] = "No se solicitaron prestaciones";
            } elseif (count($prestaciones_solicitadas) > 1) {
                $errors["prestaciones_solicitadas"] = "Actualmente solo se permite una prestacion por DM 11";
            } else {
                // $length = 0;
                foreach ($prestaciones_solicitadas as $index => $value) {
                    @["prestacion_id" => $prestacion_id, "nota" => $nota] = $value;
                    $prestacion = Prestacion::find($prestacion_id);
                    if (!$prestacion) {
                        $errors["prestaciones_solicitadas.$index.prestacion"] = "La prestación no existe";
                    } else if (!$proveedor->ofrece($prestacion_id)) {
                        $errors["prestaciones_solicitadas.$index.prestacion"] = "El proveedor no ofrece esta prestacion"; //"El proveedor no ofrece la prestacion '{$prestacion->nombre}'";
                    }
                    if (strlen($nota) > 60) {
                        $errors["prestaciones_solicitadas.$index.nota"] = "Las notas no deben exceder los 60 caracteres";
                    }
                    // $length += strlen($prestacion->nombre) + strlen($nota) + 3;
                }
            }
        }

        if (count($errors)) {
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

        foreach ($prestaciones_solicitadas as $prestacion_solicitada) {
            $solicitud->prestacionesSolicitadas()->create($prestacion_solicitada, true);
        }
        DB::transaction(function () use ($solicitud) {
            $solicitud->save();
            $solicitud->url_dm11 = route("forms.dm11", [
                "numero" => $solicitud->numero
            ]);
            $solicitud->save();
        });

        return $solicitud;
    }
}
