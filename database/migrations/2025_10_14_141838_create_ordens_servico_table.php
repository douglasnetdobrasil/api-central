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
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            
            // Link para o equipamento específico do cliente (tabela criada acima)
            $table->foreignId('cliente_equipamento_id')->nullable()->constrained('cliente_equipamentos')->onDelete('set null');
            
            // Link para o usuário (técnico) da tabela 'users'
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('status', ['Aberta', 'Aguardando Aprovação', 'Aprovada', 'Em Execução', 'Aguardando Peças', 'Concluída', 'Faturada', 'Cancelada'])
                  ->default('Aberta')
                  ->index();

            $table->timestamp('data_entrada')->useCurrent();
            $table->date('data_previsao_conclusao')->nullable();
            $table->timestamp('data_conclusao')->nullable();
            
            // Manterei os campos abaixo para o caso de não querer usar a tabela de equipamentos ou para info rápida
            $table->string('equipamento', 255)->comment('Descrição do equipamento caso não seja cadastrado');
            $table->string('numero_serie', 100)->nullable();

            $table->text('defeito_relatado')->nullable();
            $table->text('laudo_tecnico')->nullable();
            $table->text('garantia')->nullable();

            $table->decimal('valor_servicos', 10, 2)->default(0.00);
            $table->decimal('valor_produtos', 10, 2)->default(0.00);
            $table->decimal('valor_desconto', 10, 2)->default(0.00);
            $table->decimal('valor_total', 10, 2)->default(0.00);

            // Link para a venda gerada após o faturamento
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};