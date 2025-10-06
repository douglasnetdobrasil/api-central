<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EmpresaScope;


class Compra extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     * @var string
     */
    protected $table = 'compras';

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'empresa_id',
        'fornecedor_id',
        'status',
        'numero_nota',
        'serie_nota',
        'chave_acesso_nfe',
        'data_emissao',
        'data_chegada',
        'valor_total_produtos',
        'valor_frete',
        'valor_total_nota',
        'observacoes',
    ];

    /**
     * Os atributos que devem ter seu tipo convertido para tipos nativos.
     */
    protected $casts = [
        'data_emissao' => 'date',
        'data_chegada' => 'date',
        'valor_total_produtos' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'valor_total_nota' => 'decimal:2',
    ];


    // --- RELACIONAMENTOS ---

    /**
     * Uma Compra PERTENCE A uma Empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Uma Compra PERTENCE A um Fornecedor.
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /**
     * Uma Compra TEM MUITOS Itens de Compra.
     */
    
     public function itens()
    {
        return $this->hasMany(ItemCompra::class);
    }

    public function movimentacoesEstoque()
    {
        return $this->morphMany(EstoqueMovimento::class, 'origem');
    }

    public function contasAPagar()
{
    return $this->hasMany(\App\Models\ContaAPagar::class);
}

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }
    

   
}