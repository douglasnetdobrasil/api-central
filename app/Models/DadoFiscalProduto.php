<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DadoFiscalProduto extends Model
{
    use HasFactory;

    protected $table = 'dados_fiscais_produto';
    public $timestamps = false;

    protected $fillable = ['produto_id', 'ncm', 'cest', 'origem', 'cfop'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}