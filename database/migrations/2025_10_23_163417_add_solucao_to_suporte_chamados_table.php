<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suporte_chamados', function (Blueprint $table) {
            $table->text('solucao_aplicada')->nullable()->after('prioridade'); // Ou onde preferir
        });
    }

    public function down(): void
    {
        Schema::table('suporte_chamados', function (Blueprint $table) {
            $table->dropColumn('solucao_aplicada');
        });
    }
};