<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_suporte_chamado_mensagens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_chamado_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamado_id')->constrained('suporte_chamados')->onDelete('cascade');
            
            // Quem enviou a mensagem:
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Se a resposta foi de um técnico');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null')->comment('Se a resposta foi do cliente');
            
            $table->text('mensagem');
            $table->enum('tipo', ['Comentário', 'Log'])->default('Comentário')->comment('Comentário do usuário ou Log automático do sistema');
            $table->boolean('interno')->default(false)->comment('Se for uma nota interna, só visível para técnicos');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('suporte_chamado_mensagens');
    }
};