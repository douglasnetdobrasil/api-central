<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_a_receber', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->integer('parcela_numero')->default(1);
            $table->integer('parcela_total')->default(1);
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->enum('status', ['pendente', 'recebida', 'atrasada'])->default('pendente');
            $table->timestamps();
            
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_a_receber');
    }
};