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
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('desconto', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('concluida'); // Ex: concluida, cancelada
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};