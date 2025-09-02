<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadeDeMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insere um conjunto de unidades de medida padrão
        DB::table('unidades_medida')->insert([
            ['nome' => 'Unidade', 'sigla' => 'UN'],
            ['nome' => 'Quilograma', 'sigla' => 'KG'],
            ['nome' => 'Grama', 'sigla' => 'GR'],
            ['nome' => 'Litro', 'sigla' => 'LT'],
            ['nome' => 'Mililitro', 'sigla' => 'ML'],
            ['nome' => 'Caixa', 'sigla' => 'CX'],
            ['nome' => 'Pacote', 'sigla' => 'PCT'],
            ['nome' => 'Dúzia', 'sigla' => 'DZ'],
        ]);
    }
}
