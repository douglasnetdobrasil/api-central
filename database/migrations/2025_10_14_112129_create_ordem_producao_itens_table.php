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
        Schema::create('ordem_producao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_producao_id')->constrained('ordens_producao')->cascadeOnDelete();
            $table->foreignId('materia_prima_id')->constrained('produtos')->restrictOnDelete();
            
            $table->decimal('quantidade_necessaria', 10, 4)->comment('Qtd total calculada para a OP');
            $table->decimal('quantidade_baixada', 10, 4)->default(0);
            $table->decimal('custo_unitario_momento', 10, 2)->comment('Custo da MP no momento da criação da OP');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_producao_itens');
    }
};