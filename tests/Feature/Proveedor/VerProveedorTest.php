<?php

namespace Tests\Feature\Proveedor;

use App\Models\Proveedor;
use App\Models\Permisos;
use App\Models\User;
use Tests\TestCase;

class VerProveedorTest extends TestCase
{

    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->getJson("/api/proveedores/0");
        $response->assertNotFound();
    }

    public function test_usuario_puede_ver()
    {
        $user = User::factory()
            ->withPermissions([Permisos::VER_PROVEEDORES])
            ->regionalLaPaz()
            ->activo()
            ->create();

        $model = Proveedor::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertOk();
        $response->assertJson($model->toArray());

        $model = Proveedor::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertOk();
        $response->assertJson($model->toArray());
    }

    public function test_usuario_puede_ver_regionalmente()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_PROVEEDORES_REGIONAL
            ])
            ->create();

        $model = Proveedor::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertOk();
        $response->assertJson($model->toArray());

        $model = Proveedor::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertForbidden();

        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_PROVEEDORES,
                Permisos::VER_PROVEEDORES_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $model = Proveedor::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = User::factory()
            ->superUser()
            ->create();

        $model = Proveedor::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertOk();
        $response->assertJson($model->toArray());
    }

    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::VER_PROVEEDORES])
            ->create();

        $model = Proveedor::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/proveedores/{$model->padded_id}");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->getJson("/api/proveedores/0");
        $response->assertUnauthorized();
    }
}
