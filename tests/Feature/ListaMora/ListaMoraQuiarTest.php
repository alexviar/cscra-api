<?php

namespace Tests\Feature\ListaMora;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Permisos;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ListaMoraQuitarTest extends TestCase
{
    protected $connectionsToTransact = ["mysql", "galeno"];

    private function assertSuccess(TestResponse $response, $item)
    {
        $response->assertOk();
        $this->assertDatabaseMissing("lista_mora", [
            "id" => $item->id
        ]);
    }

    public function test_item_not_found()
    {
        $login = $this->getSuperUser();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/0");
        $response->assertNotFound();
    }

    public function test_usuario_puede_eliminar()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::QUITAR_DE_LA_LISTA_DE_MORA
            ])
            ->create();

        //Misma regional
        $item = ListaMoraItem::factory()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/{$item->id}");
        $this->assertSuccess($response, $item);

        //Distinta regional
        $item = ListaMoraItem::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/{$item->id}");
        $this->assertSuccess($response, $item);
    }
    
    public function test_usuario_puede_registrar_solo_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::QUITAR_DE_LA_LISTA_DE_MORA_MISMA_REGIONAL
            ])
            ->create();

        //Misma regional
        $item = ListaMoraItem::factory()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/{$item->id}");
        $this->assertSuccess($response, $item);

        //Distinta regional
        $item = ListaMoraItem::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/{$item->id}");
        $response->assertForbidden();

        //Precedencia de los permisos
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::QUITAR_DE_LA_LISTA_DE_MORA,
                Permisos::QUITAR_DE_LA_LISTA_DE_MORA_MISMA_REGIONAL
            ])
            ->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/lista-mora/{$item->id}");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();
    
        $item = ListaMoraItem::factory()->create();        
        $response = $this->actingAs($login, "sanctum")
            ->deleteJson("/api/lista-mora/{$item->id}");
        $response->assertForbidden();
    }    

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();
    
        $item = ListaMoraItem::factory()->create();        
        $response = $this->actingAs($login, "sanctum")
            ->deleteJson("/api/lista-mora/{$item->id}");
        $this->assertSuccess($response, $item);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::QUITAR_DE_LA_LISTA_DE_MORA])
            ->create();
    
        $item = ListaMoraItem::factory()->create();        
        $response = $this->actingAs($login, "sanctum")
            ->deleteJson("/api/lista-mora/{$item->id}");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->deleteJson('/api/lista-mora/0', []);
        $response->assertUnauthorized();
    }
}
