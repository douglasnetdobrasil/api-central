<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_suporte_chamado_anexos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_chamado_anexos', function (Blueprint $table) {
            $table->id();
            // Vincula direto ao chamado E/OU a uma mensagem específica
            $table->foreignId('chamado_id')->constrained('suporte_chamados')->onDelete('cascade');
            $table->foreignId('mensagem_id')->nullable()->constrained('suporte_chamado_mensagens')->onDelete('set null');
            
            $table->string('caminho_arquivo');
            $table->string('nome_original');
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('suporte_chamado_anexos');
    }
};