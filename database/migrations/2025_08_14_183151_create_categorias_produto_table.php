<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_produto', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_produto');
    }
};