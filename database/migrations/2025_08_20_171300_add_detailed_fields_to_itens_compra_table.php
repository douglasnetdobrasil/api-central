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
    Schema::table('itens_compra', function (Blueprint $table) {
        // A linha que adicionava 'subtotal' foi REMOVIDA daqui.

        // Adicionando os campos de impostos
        $table->decimal('valor_frete', 10, 2)->nullable()->after('subtotal');
        $table->decimal('valor_ipi', 10, 2)->nullable()->after('valor_frete');
        $table->decimal('valor_icms', 10, 2)->nullable()->after('valor_ipi');
        $table->decimal('valor_pis', 10, 2)->nullable()->after('valor_icms');
        $table->decimal('valor_cofins', 10, 2)->nullable()->after('valor_pis');
        $table->decimal('total_item', 10, 2)->nullable()->after('valor_cofins');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_compra', function (Blueprint $table) {
            $table->dropColumn([
                // 'subtotal' foi REMOVIDO daqui.
                'valor_frete', 'valor_ipi', 'valor_icms',
                'valor_pis', 'valor_cofins', 'total_item'
            ]);
        });
    }
};
