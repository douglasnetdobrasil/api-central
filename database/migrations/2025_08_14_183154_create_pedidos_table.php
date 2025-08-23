<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor_total', 10, 2);
            $table->enum('status', ['Pendente', 'Em Separação', 'Separado', 'Em Transporte', 'Entregue', 'Cancelado'])->default('Pendente');
            $table->text('observacao')->nullable();
            $table->timestamp('data_pedido')->useCurrent();
            
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('vendedor_id')->constrained('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};