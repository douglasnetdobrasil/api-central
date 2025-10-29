<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Adiciona a chave estrangeira para a venda
            $table->foreignId('venda_id')
                  ->nullable()
                  ->after('observacao') // Posição opcional
                  ->constrained('vendas')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['venda_id']);
            $table->dropColumn('venda_id');
        });
    }
};