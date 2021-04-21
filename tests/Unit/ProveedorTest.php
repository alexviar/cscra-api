<?php

namespace Tests\Unit;

use App\Models\Proveedor;
use PHPUnit\Framework\TestCase;

class ProveedorTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
      $proveedores = Proveedor::buscarPorNombre("Dolor Ut");
      var_dump($proveedores);
        $this->assertTrue(true);
    }
}
