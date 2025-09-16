<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfe_id',
        'produto_id',
        'numero_item',
        'quantidade',
        'valor_unitario',
        'valor_total',
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}