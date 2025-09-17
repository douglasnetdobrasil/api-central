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
            // Adiciona apenas as colunas de alíquota que estão faltando
            $table->decimal('pis_aliquota', 5, 2)->default(0.00)->after('pis_cst');
            $table->decimal('cofins_aliquota', 5, 2)->default(0.00)->after('cofins_cst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dados_fiscais_produto', function (Blueprint $table) {
            $table->dropColumn(['pis_aliquota', 'cofins_aliquota']);
        });
    }
};