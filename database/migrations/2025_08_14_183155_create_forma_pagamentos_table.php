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
        Schema::create('forma_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
    
            $table->string('nome')->comment('Ex: Cartão de Crédito 30 dias, PIX, Boleto 15 DDL');
            $table->enum('tipo', ['a_vista', 'a_prazo'])->default('a_vista');
            $table->integer('numero_parcelas')->default(1);
            $table->integer('dias_intervalo')->default(30)->comment('Dias de intervalo entre as parcelas');
    
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forma_pagamentos');
    }
};
