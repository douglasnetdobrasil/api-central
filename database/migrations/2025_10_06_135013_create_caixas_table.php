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
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para a empresa
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            // Chave estrangeira para o usuário que abriu o caixa
            $table->foreignId('user_id')->constrained('users')->comment('Usuário que abriu o caixa');
            
            // Status do caixa
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            
            // Valores de abertura e fechamento
            $table->decimal('valor_abertura', 10, 2);
            $table->decimal('valor_fechamento', 10, 2)->nullable();
            
            // Timestamps de abertura e fechamento
            $table->timestamp('data_abertura')->useCurrent();
            $table->timestamp('data_fechamento')->nullable();
            
            // Timestamps padrão do Laravel (created_at e updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};