<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recebimento extends Model
{
    use HasFactory;

    /**
     * Desativa a proteção de mass assignment.
     */
    protected $guarded = [];

    /**
     * Define os casts de tipo para os atributos.
     */
    protected $casts = [
        'data_recebimento' => 'date',
        'valor_recebido' => 'decimal:2',
        'juros' => 'decimal:2',
        'multa' => 'decimal:2',
        'desconto' => 'decimal:2',
    ];

    /**
     * Relação: Um recebimento pertence a uma Conta a Receber.
     */
    public function contaAReceber(): BelongsTo
    {
        return $this->belongsTo(ContaAReceber::class, 'conta_a_receber_id');
    }

    /**
     * Relação: Um recebimento pertence a uma Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relação: Um recebimento pode ter uma Forma de Pagamento.
     */
    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class);
    }
}