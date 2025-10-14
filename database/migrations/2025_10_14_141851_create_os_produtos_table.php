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
        Schema::create('os_produtos', function (Blueprint $table) {
            $table->id();
            // Se a OS for deletada, seus itens vão junto
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            // Não permite deletar um produto se ele estiver em uma OS
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('restrict');
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('os_produtos');
    }
};