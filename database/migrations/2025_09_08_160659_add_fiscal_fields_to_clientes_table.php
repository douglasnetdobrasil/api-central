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
        Schema::table('clientes', function (Blueprint $table) {
            // Adiciona os campos após a coluna 'cpf_cnpj'
            $table->after('cpf_cnpj', function ($table) {
                $table->string('ie')->nullable()->comment('Inscrição Estadual do cliente');
            });
            // Adiciona o código do município após a coluna 'cidade'
            $table->after('cidade', function ($table) {
                $table->integer('codigo_municipio')->nullable()->comment('Código IBGE do município do cliente');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['ie', 'codigo_municipio']);
        });
    }
};
