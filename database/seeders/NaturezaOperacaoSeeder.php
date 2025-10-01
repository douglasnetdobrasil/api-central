<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NaturezaOperacaoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('natureza_operacoes')->truncate();

        DB::table('natureza_operacoes')->insert([
            // Adiciona o campo 'cfop' para cada registro
            ['descricao' => 'Venda de mercadoria', 'cfop' => '5102'],
            ['descricao' => 'Devolução de compra para comercialização', 'cfop' => '5202'],
            ['descricao' => 'Remessa para conserto', 'cfop' => '5915'],
            ['descricao' => 'Simples remessa', 'cfop' => '5949'],
            ['descricao' => 'Venda de ativo imobilizado', 'cfop' => '5551'],
        ]);
    }
}
