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
        Schema::table('dados_fiscais_produto', function (Blueprint $table) {
            // Adiciona as colunas fiscais que estavam em falta, posicionando-as após a coluna 'cfop'
            $table->string('icms_cst', 3)->nullable()->after('cfop');
            $table->string('pis_cst', 2)->nullable()->after('icms_cst');
            $table->string('cofins_cst', 2)->nullable()->after('pis_cst');
            $table->string('csosn', 4)->nullable()->after('cofins_cst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dados_fiscais_produto', function (Blueprint $table) {
            $table->dropColumn(['icms_cst', 'pis_cst', 'cofins_cst', 'csosn']);
        });
    }
};