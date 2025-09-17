<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dados_fiscais_produto', function (Blueprint $table) {
            // Adiciona campos específicos para o ICMS de empresas do Regime Normal
            $table->string('icms_mod_bc', 1)->nullable()->comment('Modalidade de determinação da BC do ICMS')->after('icms_cst');
            $table->decimal('icms_aliquota', 5, 2)->default(0.00)->comment('Alíquota do ICMS em %')->after('icms_mod_bc');
            $table->decimal('icms_reducao_bc', 5, 2)->default(0.00)->comment('Percentual de redução da BC do ICMS')->after('icms_aliquota');
        });
    }

    public function down(): void
    {
        Schema::table('dados_fiscais_produto', function (Blueprint $table) {
            $table->dropColumn(['icms_mod_bc', 'icms_aliquota', 'icms_reducao_bc']);
        });
    }
};