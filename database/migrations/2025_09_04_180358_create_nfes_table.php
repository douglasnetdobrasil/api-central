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
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('venda_id')->constrained('vendas');
            
            $table->string('status');
            $table->string('chave_acesso', 44)->unique()->nullable();
            $table->integer('numero_nfe');
            $table->integer('serie');
            $table->string('ambiente');
            
            $table->text('caminho_xml')->nullable();
            $table->text('caminho_danfe')->nullable();
            
            $table->text('justificativa_cancelamento')->nullable();
            $table->text('mensagem_erro')->nullable();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfes');
    }
};
