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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('cnpj', 18)->unique();
            
            // A COLUNA MAIS IMPORTANTE: O "SELETOR DE NICHO"
            $table->enum('nicho_negocio', ['mercado', 'oficina', 'restaurante', 'loja_roupas'])
                  ->comment('Define o tipo de negócio para adaptar a interface e as regras');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};