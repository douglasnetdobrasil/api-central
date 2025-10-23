<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_chamados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            
            // Opcional: Equipamento que o cliente já tem cadastrado
            $table->foreignId('cliente_equipamento_id')->nullable()->constrained('cliente_equipamentos')->onDelete('set null');
            
            // O técnico que está atendendo o TICKET
            $table->foreignId('tecnico_atribuido_id')->nullable()->constrained('users')->onDelete('set null');
            
            // O PONTO DE INTEGRAÇÃO: O ID da OS gerada
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servico')->onDelete('set null');
            
            $table->string('protocolo', 20)->unique()->comment('Protocolo único do chamado (Ex: 202510-001)');
            $table->string('titulo');
            $table->text('descricao_problema')->comment('Relato inicial do cliente');
            
            $table->string('status', 50)->default('Aberto')->comment('Aberto, Em Atendimento, Aguardando Cliente, Resolvido Online, Convertido em OS, Fechado');
            $table->string('prioridade', 50)->default('Média')->comment('Baixa, Média, Alta, Urgente');

            $table->timestamp('data_resolucao')->nullable();
            $table->timestamp('data_fechamento')->nullable();
            $table->timestamps(); // data_abertura será o created_at
        });
    }

    public function down(): void {
        Schema::dropIfExists('suporte_chamados');
    }
};