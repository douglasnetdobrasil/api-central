<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regras_tributarias', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Ex: "Venda Simples Nacional para Consumidor Final - SP"
            $table->boolean('ativo')->default(true);

            // --- GATILHOS DA REGRA (colunas para o 'WHERE') ---
            $table->string('cfop', 4);
            $table->string('uf_origem', 2)->nullable()->comment('Nulo para Todas');
            $table->string('uf_destino', 2)->nullable()->comment('Nulo para Todas');
            $table->tinyInteger('crt_emitente')->nullable()->comment('Nulo para Todos. 1=SN, 3=Normal');

            // --- RESULTADOS DA REGRA (dados fiscais a serem aplicados) ---
            // ICMS
            $table->string('icms_origem', 1)->default('0');
            $table->string('icms_cst', 2)->nullable()->comment('Para Regime Normal');
            $table->string('csosn', 3)->nullable()->comment('Para Simples Nacional');
            $table->integer('icms_mod_bc')->nullable();
            $table->decimal('icms_aliquota', 10, 2)->default(0);
            $table->decimal('icms_reducao_bc', 10, 2)->default(0);

            // ICMS-ST (Substituição Tributária)
            $table->integer('icms_mod_bc_st')->nullable();
            $table->decimal('mva_st', 10, 2)->default(0)->comment('Margem de Valor Agregado %');
            $table->decimal('icms_aliquota_st', 10, 2)->default(0);

            // IPI
            $table->string('ipi_cst', 2)->nullable();
            $table->string('ipi_codigo_enquadramento', 3)->default('999');
            $table->decimal('ipi_aliquota', 10, 2)->default(0);

            // PIS
            $table->string('pis_cst', 2)->nullable();
            $table->decimal('pis_aliquota', 10, 2)->default(0);

            // COFINS
            $table->string('cofins_cst', 2)->nullable();
            $table->decimal('cofins_aliquota', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_tributarias');
    }
};