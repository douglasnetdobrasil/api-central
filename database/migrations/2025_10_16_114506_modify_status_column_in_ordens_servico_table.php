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
        // Esta função muda a coluna 'status' de ENUM para VARCHAR(50)
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->string('status', 50)->default('Aberta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta função desfaz a alteração, caso seja necessário
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->enum('status', [
                'Aberta', 'Aguardando Aprovação', 'Aprovada', 'Em Execução', 
                'Aguardando Peças', 'Concluída', 'Faturada', 'Cancelada'
            ])->default('Aberta')->change();
        });
    }
};