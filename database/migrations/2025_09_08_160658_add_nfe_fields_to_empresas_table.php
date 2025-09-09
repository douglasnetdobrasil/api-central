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
        Schema::table('empresas', function (Blueprint $table) {
            // Adiciona os campos após a coluna 'ambiente_nfe' que já existe
            $table->after('ambiente_nfe', function ($table) {
                $table->string('csc_nfe')->nullable()->comment('Código de Segurança do Contribuinte para emissão de NFC-e/NF-e');
                $table->string('csc_id_nfe')->nullable()->comment('ID do CSC (geralmente 000001 ou 000002)');
            });
             // Adiciona o código do estado após a coluna 'uf'
             $table->after('uf', function ($table) {
                $table->integer('codigo_uf')->nullable()->comment('Código IBGE da Unidade da Federação');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['csc_nfe', 'csc_id_nfe', 'codigo_uf']);
        });
    }
};
