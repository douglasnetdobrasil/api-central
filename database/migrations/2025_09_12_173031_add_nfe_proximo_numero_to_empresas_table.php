<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Adiciona o campo para o próximo número da NFe, série 1.
            // Pode ser nulo, pois empresas antigas não terão essa configuração.
            $table->integer('nfe_proximo_numero')->nullable()->after('csc_id_nfe');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('nfe_proximo_numero');
        });
    }
};