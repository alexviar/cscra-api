<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
// use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function getSuperUser(){
        return User::where("username", "admin")->first();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // RefreshDatabaseState::$migrated = true;
    }
}
