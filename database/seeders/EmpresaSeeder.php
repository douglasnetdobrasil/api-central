<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria a empresa padrão para o sistema
        DB::table('empresas')->insert([
            'crt' => '1',
            'razao_social' => 'Empresa Padrão LTDA',
            'cnpj' => '99.999.999/0001-91', // CNPJ padrão para testes
            'nicho_negocio' => 'mercado', // Nicho padrão, você pode escolher outro
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
