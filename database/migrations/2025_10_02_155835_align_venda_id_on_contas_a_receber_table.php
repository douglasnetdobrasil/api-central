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
            // Passo 1: Dropar a chave estrangeira antiga.
            $table->dropForeign('contas_a_receber_pedido_id_foreign');

            // Passo 2: Renomear a coluna.
            $table->renameColumn('pedido_id', 'venda_id');

            // Passo 3: <<-- A CORREÇÃO ESTÁ AQUI -->>
            // Garantir que a coluna aceite nulos ANTES de adicionar a nova regra.
            $table->unsignedBigInteger('venda_id')->nullable()->change();

            // Passo 4: Adicionar a nova chave estrangeira correta.
            $table->foreign('venda_id')
                  ->references('id')
                  ->on('vendas')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            // Desfaz na ordem inversa
            $table->dropForeign(['venda_id']);
            $table->renameColumn('venda_id', 'pedido_id');
            
            // Garante que a coluna volte ao seu estado original (NOT NULL)
            $table->unsignedBigInteger('pedido_id')->nullable(false)->change();

            // Recria a FK antiga apontando para a tabela 'pedidos'
            $table->foreign('pedido_id')->references('id')->on('pedidos');
        });
    }
};