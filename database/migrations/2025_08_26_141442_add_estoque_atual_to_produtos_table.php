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
        Schema::table('produtos', function (Blueprint $table) {
            // Adiciona a coluna para controlar o estoque.
            // O tipo decimal é bom para quantidades fracionadas. Se for sempre inteiro, pode usar integer().
            // `default(0)` garante que produtos antigos comecem com estoque zero.
            // `after('preco_venda')` posiciona a coluna no banco de dados para melhor organização.
            $table->decimal('estoque_atual', 10, 3)->default(0)->after('preco_venda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('estoque_atual');
        });
    }
};