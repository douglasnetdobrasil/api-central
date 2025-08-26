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
        Schema::table('produtos', function (Blueprint $table) {
            // Adiciona a coluna para o código de barras
            // ->nullable() permite que produtos sem código de barras sejam salvos
            // ->unique() garante que não existam dois produtos com o mesmo código de barras
            // ->after('nome') organiza a coluna na tabela, colocando-a depois da coluna 'nome'
            $table->string('codigo_barras', 30)->nullable()->unique()->after('nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Remove a coluna caso seja necessário reverter a migration
            $table->dropColumn('codigo_barras');
        });
    }
};