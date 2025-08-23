<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalhes_item_mercado', function (Blueprint $table) {
            $table->id();

            // --- Identificação e Organização ---
            $table->string('marca', 100)->nullable()->comment('Ex: Nestlé, Coca-Cola');
            $table->string('codigo_barras', 100)->nullable()->unique()->comment('Código EAN/UPC, essencial para o PDV com leitor');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_produto')->onDelete('set null');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('restrict');

            // --- Preços ---
            $table->decimal('preco_custo', 10, 2)->default(0.00)->comment('Quanto você pagou pelo produto');
            $table->decimal('preco_promocional', 10, 2)->nullable()->comment('Preço para promoções com data definida');
            $table->date('data_inicio_promocao')->nullable();
            $table->date('data_fim_promocao')->nullable();

            // --- Estoque e Logística ---
            $table->decimal('estoque_atual', 10, 3)->default(0.000);
            $table->decimal('estoque_minimo', 10, 3)->default(0.000)->comment('Para relatórios de ponto de pedido');
            $table->foreignId('unidade_medida_id')->constrained('unidades_medida')->comment('UN, KG, PC, CX...');
            $table->boolean('controla_validade')->default(false)->comment('Se TRUE, o sistema deve controlar lotes e datas de validade');
            $table->boolean('vendido_por_peso')->default(false)->comment('Se TRUE, o PDV solicitará o peso da balança');
            
            // Adicione mais campos conforme sua necessidade...
            // $table->string('localizacao_loja')->nullable()->comment('Ex: Corredor 5, Prateleira 3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalhes_item_mercado');
    }
};