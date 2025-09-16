<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfe extends Model
{
    use HasFactory;

    /**
     * A tabela associada a este model.
     *
     * @var string
     */
    protected $table = 'nfes';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'empresa_id',
        'venda_id',
        'status',
        'chave_acesso',
        'numero_nfe',
        'serie',
        'ambiente',
        'caminho_xml',
        'caminho_danfe',
        'justificativa_cancelamento',
        'mensagem_erro',
        'protocolo_autorizacao',
        'cce_sequencia_evento',
    ];

    /**
     * Relação: Uma NFe pertence a uma Venda.
     */
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    /**
     * Relação: Uma NFe pertence a uma Empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cces()
{
    return $this->hasMany(Cce::class)->orderBy('sequencia_evento', 'asc');
}

public function items()
{
    return $this->hasMany(NfeItem::class);
}
}