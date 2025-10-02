<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaAReceber extends Model
{
    use HasFactory;
    protected $table = 'contas_a_receber';
    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'venda_id',
        'descricao',
        'parcela_numero',
        'parcela_total',
        'valor',
        'valor_recebido',
        'data_vencimento',
        'status'
    ];
    protected $casts = [
        'valor' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'data_vencimento' => 'date',
    ];

    /**
     * <<-- ESTA É A VERSÃO CORRETA DO RELACIONAMENTO -->>
     * Define a relação direta com o Cliente, usando a coluna 'cliente_id'.
     * Isto é o que faz as contas manuais funcionarem.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Define a relação com a Venda, para contas geradas pelo PDV.
     */
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(Recebimento::class, 'conta_a_receber_id');
    }

    public function getValorPendenteAttribute(): float
    {
        return (float) bcsub((string)$this->valor, (string)$this->valor_recebido, 2);
    }
}