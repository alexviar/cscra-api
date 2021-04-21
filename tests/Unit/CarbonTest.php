<?php

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CarbonTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAddMonthTest()
    {
        $date = CarbonImmutable::createFromFormat("d/m/Y", "31/12/2019", "America/La_Paz");
        var_dump($date->addMonth(3)->format("d/m/Y"), $date->addDays(90)->format("d/m/Y"), $date->format("d/m/Y"));
        $this->assertTrue(true);
    }
}
