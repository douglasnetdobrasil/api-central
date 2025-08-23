<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_pedido', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_unitario_venda', 10, 2)->comment('Preço no momento da venda');
            $table->decimal('subtotal', 10, 2);

            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('restrict');
            $table->foreignId('unidade_medida_id')->constrained('unidades_medida')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_pedido');
    }
};