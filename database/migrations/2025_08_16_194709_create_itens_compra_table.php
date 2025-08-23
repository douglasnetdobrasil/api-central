<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');
            
            // O produto_id pode ser NULO durante a digitação,
            // pois o produto pode ainda não existir no sistema.
            $table->foreignId('produto_id')->nullable()->constrained('produtos');

            $table->string('descricao_item_nota'); // Descrição do produto como vem na nota
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_custo_nota', 10, 4); // Custo unitário na nota
            $table->decimal('subtotal', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_compra');
    }
};