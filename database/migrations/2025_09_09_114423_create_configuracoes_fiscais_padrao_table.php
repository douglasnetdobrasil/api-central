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
        Schema::create('configuracoes_fiscais_padrao', function (Blueprint $table) {
            $table->id();
            
            // Um perfil pertence a uma empresa
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');

            // --- IDENTIFICAÇÃO DO PERFIL ---
            $table->string('nome_perfil')->comment('Nome para identificar este conjunto de regras. Ex: Padrão Vendas Simples');

            // --- PADRÕES GERAIS DO PRODUTO ---
            $table->string('ncm_padrao', 10)->nullable();
            $table->string('cfop_padrao', 4)->nullable();
            $table->string('origem_padrao', 1)->default('0');

            // --- PADRÕES DE TRIBUTAÇÃO ---
            $table->string('csosn_padrao', 4)->nullable();
            $table->string('icms_cst_padrao', 3)->nullable();
            $table->string('pis_cst_padrao', 2)->nullable();
            $table->string('cofins_cst_padrao', 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_fiscais_padrao');
    }
};
