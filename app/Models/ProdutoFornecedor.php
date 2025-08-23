<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoFornecedor extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     * @var string
     */
    protected $table = 'produto_fornecedores';

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'produto_id',
        'fornecedor_id',
        'codigo_produto_fornecedor',
        'preco_custo_ultima_compra',
        'data_ultima_compra',
    ];

    /**
     * Os atributos que devem ter seu tipo convertido para tipos nativos.
     */
    protected $casts = [
        'preco_custo_ultima_compra' => 'decimal:4',
        'data_ultima_compra' => 'date',
    ];


    // --- RELACIONAMENTOS ---

    /**
     * Este vínculo PERTENCE A um Produto do nosso sistema.
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Este vínculo PERTENCE A um Fornecedor.
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}