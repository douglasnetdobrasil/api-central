<?php
// Arquivo: app/Models/OsServico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsServico extends Model
{
    use HasFactory;

    protected $table = 'os_servicos';

    protected $fillable = [
        'ordem_servico_id',
        'servico_id',
        'tecnico_id', // Importante que este campo esteja no fillable
        'quantidade',
        'preco_unitario',
        'subtotal',
    ];

    /**
     * Relacionamento para buscar os dados do serviço (que é um produto).
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'servico_id');
    }

    /**
     * Relacionamento para buscar a OS deste item.
     */
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    /**
     * ==========================================================
     * ||||||||||||||||||| AQUI ESTÁ A CORREÇÃO |||||||||||||||||||
     * ==========================================================
     * Relacionamento para buscar o técnico que executou o serviço.
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}