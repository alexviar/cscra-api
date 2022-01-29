<?php

namespace Tests\Feature\Proveedor;

use App\Models\Medico;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CambiarEstadoProveedorTest extends TestCase
{

    private function assertSuccess(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        if ($model->tipo == 1) {
            $this->assertDatabaseHas("proveedores", [
                "id" => $model->id,
                "tipo" => 1,
                "estado" => $data["estado"],
                "nit" => $model->nit,
                "ci" => $model->ci->raiz,
                "ci_complemento" => $model->ci->complemento,
                "apellido_paterno" => $model->apellido_paterno,
                "apellido_materno" => $model->apellido_materno,
                "nombre" => $model->nombre,
                "especialidad" => $model->especialidad,
                "regional_id" => $model->regional_id,
                "direccion" => $model->direccion,
                "ubicacion" => DB::raw("point(" . $model->ubicacion->getLng() . "," . $model->ubicacion->getLat() . ")"),
                "telefono1" => $model->telefono1,
                "telefono2" => $model->telefono2
            ]);
        } else {
            $this->assertDatabaseHas("proveedores", [
                "id" => $model->id,
                "tipo" => 2,
                "estado" => $data["estado"],
                "nit" => $model->nit,
                "nombre" => $model->nombre,
                "regional_id" => $model->regional_id,
                "direccion" => $model->direccion,
                "ubicacion" => DB::raw("point(" . $model->ubicacion->getLng() . "," . $model->ubicacion->getLat() . ")"),
                "telefono1" => $model->telefono1,
                "telefono2" => $model->telefono2
            ]);
        }
    }

    public function test_proveedor_no_existe(){
        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/0/actualizar-estado", []);
        $response->assertNotFound();
    }

    public function test_campos_requeridos()
    {
        $login = $this->getSuperUser();

        $proveedor = Proveedor::factory()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", []);
        $response->assertJsonValidationErrors([
            "estado" => "Este campo es requerido"
        ]);
    }

    public function test_estado_no_valido()
    {
        $login = $this->getSuperUser();

        $proveedor = Proveedor::factory()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", [
                "estado" => 3
            ]);

        $response->assertJsonValidationErrors([
            "estado" => "Estado invalido"
        ]);
    }

    public function test_usuario_puede_cambiar_estado()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_ESTADO_PROVEEDORES
            ])
            ->create();

        //Misma regional
        $proveedor = Proveedor::factory()
            ->regionalLaPaz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);

        
        //Distinta regional
        $proveedor = Proveedor::factory()
            ->regionalSantaCruz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);
    }

    public function test_usuario_puede_cambiar_estados_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_ESTADO_PROVEEDORES_REGIONAL
            ])
            ->create();

        //Misma regional
        $proveedor = Proveedor::factory()
            ->regionalLaPaz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);

        
        //Distinta regional
        $proveedor = Proveedor::factory()
            ->regionalSantaCruz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $response->assertForbidden();

        //Precedencia de los permisos regionales
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_ESTADO_PROVEEDORES,
                Permisos::ACTUALIZAR_ESTADO_PROVEEDORES_REGIONAL
            ])
            ->create();
        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        /** @var User $login */
        $login = User::factory()
            ->create();

        $proveedor = Proveedor::factory()
            ->baja()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", [
                "estado" => 1
            ]);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $proveedor = Proveedor::factory()
            ->create();

        $data = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $this->assertSuccess($response, $proveedor, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_MEDICOS])
            ->create();

        $proveedor = Proveedor::factory()
            ->create();

        $data = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}/actualizar-estado", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson("/api/proveedores/0/actualizar-estado", [
                "estado" => 1
            ]);
        $response->assertUnauthorized();
    }    
}
