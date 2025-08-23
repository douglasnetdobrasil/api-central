<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            // Adiciona uma coluna para definir o tipo (Pessoa Física ou Jurídica)
            $table->enum('tipo_pessoa', ['fisica', 'juridica'])->default('juridica')->after('nome_fantasia');

            // Renomeia a coluna 'cnpj' para um nome mais genérico
            $table->renameColumn('cnpj', 'cpf_cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->renameColumn('cpf_cnpj', 'cnpj');
            $table->dropColumn('tipo_pessoa');
        });
    }
};