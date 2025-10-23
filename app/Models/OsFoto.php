<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsFoto extends Model
{
    use HasFactory;

    protected $table = 'os_fotos';

    protected $fillable = [
        'ordem_servico_id',
        'caminho_arquivo',
        'descricao',
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    // Accessor para facilitar a exibição da imagem
    public function getUrlAttribute(): string
    {
        // Certifique-se de que o link de storage está configurado (php artisan storage:link)
        return \Illuminate\Support\Facades\Storage::url($this->caminho_arquivo);
    }
}