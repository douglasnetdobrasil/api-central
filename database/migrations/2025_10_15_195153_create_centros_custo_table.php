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
        Schema::create('centros_custo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            
            // Coluna para a estrutura hierárquica (pai-filho)
            $table->foreignId('parent_id')->nullable()->constrained('centros_custo')->cascadeOnDelete();

            $table->string('nome');
            $table->string('codigo', 50)->nullable();
            
            // SINTETICO = Agrupador (não aceita lançamentos); ANALITICO = Aceita lançamentos
            $table->enum('tipo', ['SINTETICO', 'ANALITICO'])->default('ANALITICO');
            
            // Flags para flexibilizar o uso do centro de custo
            $table->boolean('aceita_despesas')->default(true);
            $table->boolean('aceita_receitas')->default(true);
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();

            // Índices para otimizar consultas e garantir unicidade
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_custo');
    }
};