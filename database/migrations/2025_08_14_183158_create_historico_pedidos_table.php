<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao_acao');
            $table->timestamp('data_acao')->useCurrent();

            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_pedidos');
    }
};