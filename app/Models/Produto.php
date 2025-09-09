<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Configuracao;
use App\Models\Scopes\EmpresaScope;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'nome',
        'preco_venda',
        'codigo_barras',
        'ativo',
        'detalhe_id',
        'margem_lucro',
        'estoque_atual',
        'detalhe_type', // <-- ESTA É A LINHA CRUCIAL QUE FALTAVA
        'preco_custo',
        'categoria_id',
        'empresa_id',
        'unidade',
    ];

    /**
     * Atributos que devem ter seu tipo convertido.
     */
    protected $casts = [
        'preco_venda' => 'decimal:2',
        'ativo' => 'boolean',
        'preco_custo' => 'decimal:2',    // ADICIONADO
        'margem_lucro' => 'decimal:2',   // ADICIONADO
    ];


    // --- RELACIONAMENTOS ---

    /**
     * Pega o modelo de detalhe associado (DetalheItemMercado, DetalheServicoOficina, etc).
     */
    public function detalhe()
    {
        return $this->morphTo();
    }

    /**
     * Um produto TEM UM conjunto de dados fiscais.
     */
    public function dadosFiscais()
    {
        return $this->hasOne(DadoFiscalProduto::class);
    }

    /**
     * ADICIONADO: Um produto PERTENCE A UMA Categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

  //  protected static function booted(): void
   // {
    //    static::addGlobalScope(new EmpresaScope);
   // }

    


    // --- LÓGICA DE NEGÓCIO ---

    /**
     * ADICIONADO: Calcula o preço de venda com base no custo e na hierarquia de margens.
     *
     * @param float $precoCusto O preço de custo do produto.
     * @return float O preço de venda calculado.
     */
    public function calcularPrecoVenda(float $precoCusto): float
    {
        $margem = 0;

        // 1. Verifica a margem do próprio produto
        if ($this->margem_lucro !== null) {
            $margem = $this->margem_lucro;
        }
        // 2. Se não houver, verifica a margem da categoria
        elseif ($this->categoria && $this->categoria->margem_lucro !== null) {
            $margem = $this->categoria->margem_lucro;
        }
        // 3. Se não houver, busca a margem padrão do banco de dados
        else {
            $margem = Configuracao::where('chave', 'margem_lucro_padrao')->first()->valor ?? 100.00;
        }

        // Fórmula: Preço de Venda = Custo * (1 + (Margem / 100))
        $precoVenda = $precoCusto * (1 + ($margem / 100));

        // Arredonda para 2 casas decimais
        return round($precoVenda, 2);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    

    public function cotacoes()
{
    return $this->belongsToMany(Cotacao::class, 'cotacao_produto')->withPivot('quantidade');
}
}