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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            
            // Chaves estrangeiras
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Dados do inventário
            $table->timestamp('data_inicio');
            $table->timestamp('data_conclusao')->nullable();
            
            // Status possíveis: 'planejado', 'em_andamento', 'contado', 'finalizado'
            $table->string('status')->default('planejado');
            
            $table->text('observacoes')->nullable();
            $table->timestamps(); // Cria as colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};