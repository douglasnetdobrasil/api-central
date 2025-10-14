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
        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();

            // Chaves estrangeiras
            $table->foreignId('inventario_id')->constrained('inventarios')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');

            // Dados da contagem
            // Usamos decimal para precisão em produtos vendidos por peso/fração (ex: 1.250 kg)
            $table->decimal('estoque_esperado', 10, 3);
            $table->decimal('quantidade_contada', 10, 3)->nullable();
            $table->decimal('diferenca', 10, 3)->default(0);

            $table->timestamps(); // Cria as colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
    }
};