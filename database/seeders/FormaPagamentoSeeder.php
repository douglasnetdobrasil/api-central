<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormaPagamento;
use Illuminate\Support\Facades\DB;

class FormaPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos DB::table para inserir os dados, pois a model FormaPagamento
        // tem um escopo global (EmpresaScope) que não queremos aplicar aqui,
        // já que a seeder pode ser rodada sem um usuário logado.
        
        DB::table('forma_pagamentos')->insert([
            [
                'empresa_id'      => 1, // Será preenchido quando um novo usuário se cadastrar
                'nome'            => 'Dinheiro',
                'codigo_sefaz'    => '01',
                'tipo'            => 'a_vista',
                'numero_parcelas' => 1,
                'dias_intervalo'  => 0,
                'ativo'           => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'empresa_id'      => 1,
                'nome'            => 'Pix',
                'codigo_sefaz'    => '17',
                'tipo'            => 'a_vista',
                'numero_parcelas' => 1,
                'dias_intervalo'  => 0,
                'ativo'           => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'empresa_id'      => 1,
                'nome'            => 'Cartão de Débito',
                'codigo_sefaz'    => '04',
                'tipo'            => 'a_vista',
                'numero_parcelas' => 1,
                'dias_intervalo'  => 0,
                'ativo'           => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'empresa_id'      => 1,
                'nome'            => 'Cartão de Crédito',
                'codigo_sefaz'    => '03',
                'tipo'            => 'a_prazo', // Crédito é considerado "a prazo" mesmo em 1x
                'numero_parcelas' => 1,
                'dias_intervalo'  => 30,
                'ativo'           => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}