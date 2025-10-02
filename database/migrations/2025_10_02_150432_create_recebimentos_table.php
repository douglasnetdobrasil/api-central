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
        Schema::create('recebimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_a_receber_id')->constrained('contas_a_receber')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('forma_pagamentos')->onDelete('set null');
            
            $table->decimal('valor_recebido', 15, 2)->comment('Valor efetivamente recebido nesta transação');
            $table->decimal('juros', 15, 2)->nullable()->default(0.00);
            $table->decimal('multa', 15, 2)->nullable()->default(0.00);
            $table->decimal('desconto', 15, 2)->nullable()->default(0.00);
            
            $table->date('data_recebimento');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recebimentos');
    }
};