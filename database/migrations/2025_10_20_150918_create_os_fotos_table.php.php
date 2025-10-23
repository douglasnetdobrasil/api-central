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
        Schema::create('os_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            $table->string('caminho_arquivo')->comment('Caminho no storage: Ex: os/123/foto_1.jpg');
            $table->string('descricao')->nullable()->comment('Ex: Foto do problema na tela');
            $table->timestamps(); // Para saber quando a foto foi adicionada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('os_fotos');
    }
};