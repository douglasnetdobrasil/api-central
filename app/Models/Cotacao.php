<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;
    protected $table = 'cotacoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'descricao',
        'data_cotacao',
        'status',
    ];

    protected $casts = [
        'data_cotacao' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    // Relacionamento: Uma cotação tem muitos PRODUTOS
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'cotacao_produto')
                    ->withPivot('quantidade');
    }

    // Relacionamento: Uma cotação tem muitos FORNECEDORES
    public function fornecedores()
    {
        return $this->belongsToMany(Fornecedor::class, 'cotacao_fornecedor');
    }

    // Relacionamento: Uma cotação tem muitas RESPOSTAS
    public function respostas()
    {
        return $this->hasMany(CotacaoResposta::class);
    }
}