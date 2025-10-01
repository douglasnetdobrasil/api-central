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
        // O nome da tabela agora é 'conta_pagamentos'
        Schema::create('conta_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_a_pagar_id')->constrained('contas_a_pagar')->onDelete('cascade');
            $table->foreignId('forma_pagamento_id')->constrained('forma_pagamentos');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->decimal('valor', 15, 2);
            $table->date('data_pagamento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_pagamentos');
    }
};
