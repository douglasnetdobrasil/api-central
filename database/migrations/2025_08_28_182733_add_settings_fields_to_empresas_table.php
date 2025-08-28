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
            $table->string('nome_fantasia')->after('razao_social')->nullable();
            $table->string('inscricao_estadual')->after('cnpj')->nullable();
            $table->string('endereco')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
        });
    }
    
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'nome_fantasia',
                'inscricao_estadual',
                'endereco',
                'telefone',
                'email',
                'website',
                'logo_path',
            ]);
        });
    }
};
