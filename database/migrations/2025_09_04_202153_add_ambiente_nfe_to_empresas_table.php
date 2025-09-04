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
            // 1 = Produção, 2 = Homologação (Testes)
            $table->tinyInteger('ambiente_nfe')->default(2)->after('crt');
        });
    }
    
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('ambiente_nfe');
        });
    }
};
