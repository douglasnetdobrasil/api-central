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
        Schema::create('categoria_contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
    
            // Hierarquia: aponta para a mesma tabela
            $table->foreignId('parent_id')->nullable()->constrained('categoria_contas_a_pagar')->onDelete('cascade');
    
            $table->string('nome');
            $table->string('cor')->default('#cccccc');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_conta_a_pagars');
    }
};
