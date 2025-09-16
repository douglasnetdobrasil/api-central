<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NaturezaOperacaoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('natureza_operacoes')->insert([
            ['descricao' => 'Venda de mercadoria'],
            ['descricao' => 'Devolução de compra para comercialização'],
            ['descricao' => 'Remessa para conserto'],
            ['descricao' => 'Simples remessa'],
            ['descricao' => 'Venda de ativo imobilizado'],
        ]);
    }
}
