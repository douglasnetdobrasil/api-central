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
        Schema::create('ficha_tecnica_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            
            $table->foreignId('produto_acabado_id')
                  ->comment('FK para produtos (tipo = produto_acabado)')
                  ->constrained('produtos')
                  ->cascadeOnDelete();

            $table->foreignId('materia_prima_id')
                  ->comment('FK para produtos (tipo = materia_prima)')
                  ->constrained('produtos')
                  ->restrictOnDelete();
                  
            $table->decimal('quantidade', 10, 4)->comment('Qtd da matéria-prima para 1 unidade do produto acabado');
            $table->string('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ficha_tecnica_producao');
    }
};