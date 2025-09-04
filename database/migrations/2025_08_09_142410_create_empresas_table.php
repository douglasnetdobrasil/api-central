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
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj', 18)->unique();
            $table->string('ie')->nullable(); // Inscrição Estadual
            $table->string('im')->nullable();   // Inscrição Municipal
            $table->integer('crt'); // Código de Regime Tributário
    
            // Endereço
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('complemento')->nullable();
            $table->string('cep', 9)->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('codigo_municipio', 7)->nullable();
            $table->string('telefone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
    
            // Certificado Digital
            $table->text('certificado_a1_path')->nullable();
            $table->text('certificado_a1_password')->nullable();
    
            $table->enum('nicho_negocio', ['mercado', 'oficina', 'restaurante', 'loja_roupas']);
            
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