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
        Schema::table('vendas', function (Blueprint $table) {
            $table->string('nfe_chave_acesso', 44)->nullable()->after('status');
            $table->string('nfe_status')->nullable()->after('nfe_chave_acesso'); // Ex: autorizada, cancelada, erro
        });
    }
    
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['nfe_chave_acesso', 'nfe_status']);
        });
    }
};
