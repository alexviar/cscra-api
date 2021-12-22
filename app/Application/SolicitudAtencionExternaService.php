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
use Exception;
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
                "medico" => $solicitud->medico,
                "proveedor" => $solicitud->proveedor,
                "url_dm11" => $solicitud->url_dm11
            ];
        });
    }

    public function registrar($regional_id, $asegurado_id, $medico, $especialidad, $proveedor, $usuario_id, $prestaciones_solicitadas)
    {
        $asegurado = Afiliado::buscarPorId($asegurado_id);
        $hoy = Carbon::now("America/La_Paz");
        $errors = [];
        if (!$asegurado) {
            throw new Exception("El asegurado no existe");
        }
        if (!$asegurado->ultimaAfiliacion) {
            throw new Exception("No se encontraron registros de la afiliacion");
        }

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

        if ($asegurado->tipo == 2) {
            $titular = $asegurado->titular;
            if(!$titular){
                throw new Exception("Titular no encontrado");
            }
            if($asegurado->afiliacion->parentesco != 8){
                if ($titular->estado == 1) {
                    if ($asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
                } else if ($titular->estado == 2) {
                    if (!$asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja";
                } else {
                    $errors["titular.estado"] = "El asegurado tiene un estado indeterminado";
                }

                if ($titular->afiliacion->baja) {
                    if (!$titular->afiliacion->baja->fechaValidezSeguro) $errors["titular.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                    else if ($titular->afiliacion->baja->fechaValidezSeguro->lte($hoy)) $errors["titular.fecha_validez_seguro"] = "El seguro ya no tiene validez";
                }
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

        if (count($prestaciones_solicitadas) == 0) {
            $errors["prestaciones_solicitadas"] = "No se solicitaron prestaciones";
        } elseif (count($prestaciones_solicitadas) > 1) {
            $errors["prestaciones_solicitadas"] = "Actualmente solo se permite una prestacion por DM 11";
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
        $solicitud->medico = $medico;
        $solicitud->especialidad = $especialidad;
        $solicitud->proveedor = $proveedor;
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
