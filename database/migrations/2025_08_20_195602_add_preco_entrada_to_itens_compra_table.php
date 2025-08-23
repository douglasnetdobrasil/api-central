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
        Schema::table('itens_compra', function (Blueprint $table) {
            // Novo preço de custo que será usado no sistema, pode ser alterado.
            // Fica depois do preço original da nota.
            $table->decimal('preco_entrada', 10, 4)->nullable()->after('preco_custo_nota');
        });
    }
    
    public function down(): void
    {
        Schema::table('itens_compra', function (Blueprint $table) {
            $table->dropColumn('preco_entrada');
        });
    }
};
