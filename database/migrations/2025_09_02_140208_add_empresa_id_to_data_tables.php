<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lista de todas as tabelas que receberão a coluna
        $tables = [
            
           
            
            'produtos',
            'fornecedores',
            'configuracoes',
            'contas_a_pagar',
            'contas_a_receber',
            'dados_fiscais_produto',
            'detalhes_item_mercado',
            'historico_pedidos',
            'itens_compra',
            'orcamento_itens',
            'pedidos',
            'unidades_medida',
            'categorias'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Adiciona a coluna empresa_id como chave estrangeira
                $table->foreignId('empresa_id')
                      ->after('id') // Opcional: posiciona a coluna depois do ID
                      ->nullable()   // Importante: para não dar erro em tabelas com dados
                      ->constrained('empresas')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            
            'produtos',
            'clientes',
            'fornecedores',
            'orcamentos',
            'compras',
            'configuracoes',
            'contas_a_pagar',
            'contas_a_receber',
            'dados_fiscais_produto',
            'detalhes_item_mercado',
            'historico_pedidos',
            'itens_compra',
            'forma_pagamentos',
            'orcamento_itens',
            'pedidos',
            'unidades_medida',
            'categorias'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Remove a chave estrangeira e a coluna
                $table->dropForeign(['empresa_id']);
                $table->dropColumn('empresa_id');
            });
        }
    }
};