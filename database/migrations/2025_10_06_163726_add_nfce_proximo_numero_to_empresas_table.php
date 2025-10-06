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
            // Adiciona o novo campo para controlar a numeração da NFC-e
            // Colocamos depois do campo da NFe para manter a organização
            $table->integer('nfce_proximo_numero')->nullable()->after('nfe_proximo_numero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            //
        });
    }
};
