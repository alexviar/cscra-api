<?php

namespace Tests\Unit;

use App\Models\AseguradoRepository;
use PHPUnit\Framework\TestCase;

class AseguradoRepositoryTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $repo  = new AseguradoRepository();
        $RESULTS = $repo->buscarPorMatricula("01-0203-ABC");
        var_dump($RESULTS);
        $this->assertTrue(true);
    }
}
