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
        Schema::create('lancamento_centro_custo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_custo_id')->constrained('centros_custo')->cascadeOnDelete();
            
            // Colunas para a relação polimórfica
            // Elas armazenarão o ID (lancamento_id) e o Model (lancamento_type)
            // Ex: lancamento_id = 15, lancamento_type = 'App\Models\ContaPagar'
            $table->morphs('lancamento');

            $table->decimal('valor', 15, 2);
            $table->decimal('percentual', 5, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamento_centro_custo');
    }
};