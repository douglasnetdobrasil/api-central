<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissaoCaixaSeeder extends Seeder
{
    public function run(): void
    {
        // Cria a permissão, se ela ainda não existir
        Permission::findOrCreate('operar-caixa', 'web');
    }
}