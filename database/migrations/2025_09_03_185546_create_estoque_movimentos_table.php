<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estoque_movimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->string('tipo_movimento'); // 'saida_venda', 'entrada_compra', 'ajuste_positivo', 'ajuste_negativo'
            $table->decimal('quantidade', 10, 3); // A quantidade que foi movimentada

            $table->decimal('saldo_anterior', 10, 3); // Estoque ANTES da movimentação
            $table->decimal('saldo_novo', 10, 3);     // Estoque DEPOIS da movimentação

            // Relação Polimórfica: para ligar a movimentação a uma Venda, Compra, etc.
            $table->morphs('origem'); // Cria as colunas 'origem_id' e 'origem_type'

            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentos');
    }
};