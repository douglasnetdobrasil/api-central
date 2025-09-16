<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nfes')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('numero_item');
            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfe_items');
    }
};