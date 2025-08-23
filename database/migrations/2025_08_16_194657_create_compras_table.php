<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('fornecedor_id')->constrained('fornecedores');
            
            // Coluna chave para controlar o fluxo
            $table->enum('status', ['digitacao', 'conferencia', 'finalizada', 'cancelada'])->default('digitacao');

            // Dados da Nota Fiscal
            $table->string('numero_nota', 50);
            $table->string('serie_nota', 10)->nullable();
            $table->string('chave_acesso_nfe', 44)->nullable()->unique();
            $table->date('data_emissao');
            $table->date('data_chegada')->nullable();

            $table->decimal('valor_total_produtos', 10, 2)->nullable();
            $table->decimal('valor_frete', 10, 2)->default(0);
            $table->decimal('valor_total_nota', 10, 2);

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};