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
        Schema::create('ordens_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('produto_acabado_id')->constrained('produtos')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('status', 50)->default('Planejada')->comment('Ex: Planejada, Em Produção, Concluída, Cancelada');
            $table->decimal('quantidade_planejada', 10, 3);
            $table->decimal('quantidade_produzida', 10, 3)->nullable();
            
            $table->date('data_inicio_prevista')->nullable();
            $table->date('data_fim_prevista')->nullable();
            $table->timestamp('data_inicio_real')->nullable();
            $table->timestamp('data_fim_real')->nullable();
            
            $table->decimal('custo_total_estimado', 15, 2)->nullable();
            $table->decimal('custo_total_real', 15, 2)->nullable();
            
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_producao');
    }
};