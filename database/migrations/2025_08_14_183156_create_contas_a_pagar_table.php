<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id();
            
            // --- RELACIONAMENTOS ESSENCIAIS ---
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('set null');
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('forma_pagamentos');
    
            // --- DADOS DO DOCUMENTO ---
            $table->string('descricao');
            $table->string('numero_documento')->nullable();
            $table->date('data_emissao');
            $table->date('data_vencimento');
            
            // --- VALORES PARA GESTÃO DE PAGAMENTOS PARCIAIS ---
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0.00);
            
            // --- CONTROLE E OBSERVAÇÕES ---
            $table->date('data_pagamento')->nullable();
            $table->string('status')->default('A Pagar'); // Ex: 'A Pagar', 'Paga Parcialmente', 'Paga', 'Cancelada'
            $table->text('observacoes')->nullable();
    
            $table->timestamps();
        });
    
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_a_pagar');
    }
};