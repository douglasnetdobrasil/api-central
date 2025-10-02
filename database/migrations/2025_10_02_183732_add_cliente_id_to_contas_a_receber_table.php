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
        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->foreignId('cliente_id')
                  ->nullable() // Será nulo para contas que vêm de vendas
                  ->after('empresa_id') // Posiciona a coluna para melhor organização
                  ->constrained('clientes') // Cria a chave estrangeira para a tabela 'clientes'
                  ->onDelete('set null'); // Se um cliente for deletado, o campo na conta fica nulo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};