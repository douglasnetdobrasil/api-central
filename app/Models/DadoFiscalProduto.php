<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Mantive o nome da sua classe no singular: DadoFiscalProduto
class DadoFiscalProduto extends Model
{
    use HasFactory;

    protected $table = 'dados_fiscais_produto';
    public $timestamps = false;

    /**
     * CORREÇÃO: Adicionados todos os campos fiscais que o formulário irá enviar.
     */
    protected $fillable = [
        'produto_id', 
        'ncm', 
        'cest', 
        'origem', 
        'cfop',
        'icms_cst',
        'pis_cst',
        'cofins_cst',
        'csosn',
    ];

    public function produto()
    {
        // Adicionada a chave estrangeira para maior clareza
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}