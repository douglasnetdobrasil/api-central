<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->string('codigo_produto_fornecedor');
            $table->decimal('preco_custo_ultima_compra', 10, 4)->nullable();
            $table->date('data_ultima_compra')->nullable();
            $table->timestamps();

            // CORREÇÃO: Adicionado um nome customizado e mais curto para o índice
            $table->unique(['fornecedor_id', 'codigo_produto_fornecedor'], 'prod_forn_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_fornecedores');
    }
};