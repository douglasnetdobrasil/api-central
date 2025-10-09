<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaixaMovimentacao extends Model
{
    use HasFactory;
    protected $table = 'caixa_movimentacoes';
    protected $fillable = [
        'caixa_id',
        'user_id',
        'tipo',
        'valor',
        'observacao',
    ];

    // Relacionamentos (opcional, mas bom ter)
    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}