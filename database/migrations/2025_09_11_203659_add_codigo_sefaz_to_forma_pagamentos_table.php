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
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            // Adiciona o novo campo após a coluna 'nome'
            $table->string('codigo_sefaz', 2)->after('nome')->nullable()->comment('Código da forma de pagamento para a SEFAZ (tPag)');
        });
    }

    public function down(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->dropColumn('codigo_sefaz');
        });
    }
};
