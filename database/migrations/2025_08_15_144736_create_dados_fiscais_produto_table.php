<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dados_fiscais_produto', function (Blueprint $table) {
            $table->id();
            // Ligação 1-para-1 com o produto principal
            $table->foreignId('produto_id')->unique()->constrained('produtos')->onDelete('cascade');

            $table->string('ncm', 10)->nullable()->comment('Nomenclatura Comum do Mercosul (Obrigatório para NF-e/NFC-e)');
            $table->string('cest', 7)->nullable()->comment('Código Especificador da Substituição Tributária');
            $table->string('origem', 1)->default('0')->comment('Origem da mercadoria (0: Nacional, 1: Estrangeira, etc.)');
            $table->string('cfop', 4)->nullable()->comment('Código Fiscal de Operações e Prestações');
            
            // Campos para alíquotas de impostos podem ser adicionados aqui
            // Ex: $table->decimal('aliquota_icms', 5, 2)->nullable();
            // Ex: $table->decimal('aliquota_pis', 5, 2)->nullable();
            // Ex: $table->decimal('aliquota_cofins', 5, 2)->nullable();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('dados_fiscais_produto');
    }
};