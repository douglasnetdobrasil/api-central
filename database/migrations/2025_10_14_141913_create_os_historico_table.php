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
        Schema::create('os_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            // Usuário que realizou a ação
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('descricao')->comment('Ex: Status alterado para "Aprovada"');
            $table->timestamp('created_at')->useCurrent();
            // Não precisamos de updated_at para um log
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('os_historico');
    }
};