<?php


namespace Tests;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Força a ativação de chaves estrangeiras para o SQLite
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::statement('PRAGMA foreign_keys=on;');
        }
    }
}

