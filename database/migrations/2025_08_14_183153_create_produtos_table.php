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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('preco_venda', 10, 2);
            $table->boolean('ativo')->default(true);

            // Colunas Polimórficas ESSENCIAIS para a nova arquitetura
            $table->unsignedBigInteger('detalhe_id');
            $table->string('detalhe_type');

            $table->timestamps();

            // Índice para otimizar as buscas polimórficas
            $table->index(['detalhe_id', 'detalhe_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};