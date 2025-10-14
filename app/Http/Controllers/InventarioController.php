<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\InventarioItem;
use App\Models\Produto;
use App\Services\EstoqueService;
use Illuminate\Http\Request;

use App\Models\Categoria;
use App\Models\Setor;

class InventarioController extends Controller
{
    public function index()
    {
        // Busca os inventários do mais novo para o mais antigo, com paginação.
        // O with('responsavel') otimiza a busca, já carregando o nome do usuário.
        $inventarios = Inventario::with('responsavel')
                                ->latest('data_inicio')
                                ->paginate(15);
        
        return view('inventarios.index', compact('inventarios')); 
    }


    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();
        $setores = Setor::orderBy('nome')->get();
    
          
        return view('inventarios.create', compact('categorias', 'setores'));
    }

    public function store(Request $request)
    {
        
        // Validação dos campos que vêm do formulário
        $request->validate([
            'escopo' => 'required|in:completo,parcial',
            'subdivisao' => 'nullable|required_if:escopo,parcial|in:categoria,setor',
            'categoria_id' => 'nullable|required_if:subdivisao,categoria|exists:categorias,id',
            'setor_id' => 'nullable|required_if:subdivisao,setor|exists:setores,id', // Cuidado: crie a tabela/model 'setores'
            'observacoes' => 'nullable|string',
        ]);
    
        $produtosQuery = Produto::where('ativo', true);
        
        // Se o escopo for parcial, aplicamos os filtros aninhados
        if ($request->escopo === 'parcial') {
            if ($request->subdivisao === 'categoria') {
                $produtosQuery->where('categoria_id', $request->categoria_id);
            } elseif ($request->subdivisao === 'setor') {
                $produtosQuery->where('setor_id', $request->setor_id);
            }
        }
        // Se for 'completo', nenhum filtro extra é necessário, a query pega todos os produtos ativos.
    
        $produtos = $produtosQuery->get();
    
        if ($produtos->isEmpty()) {
            return back()->with('error', 'Nenhum produto encontrado para o filtro selecionado. O inventário não foi iniciado.');
        }
    
        // O resto da lógica para criar o inventário e os itens continua a mesma...
        $inventario = Inventario::create([
            'empresa_id' => auth()->user()->empresa_id,
            'user_id' => auth()->id(),
            'data_inicio' => now(),
            'status' => 'em_andamento',
            'observacoes' => $request->observacoes,
        ]);
    
        foreach ($produtos as $produto) {
            InventarioItem::create([
                'inventario_id' => $inventario->id,
                'produto_id' => $produto->id,
                'estoque_esperado' => $produto->estoque_atual,
            ]);
        }
    
        return redirect()->route('inventarios.contagem', $inventario)->with('success', 'Inventário iniciado! Pode começar a contagem.');
    }

    public function showContagem(Inventario $inventario, Request $request)
    {
        // Garante que só se pode contar um inventário "em andamento"
        if ($inventario->status !== 'em_andamento') {
            return redirect()->route('inventarios.index')->with('error', 'Este inventário não está mais em contagem.');
        }

        // Pega o termo de busca da URL (?busca=...)
        $termoBusca = $request->input('busca');

        // Inicia a query base para os itens do inventário
        $queryItens = $inventario->items()->with('produto');

        // Se houver um termo de busca, aplica o filtro
        if ($termoBusca) {
            $queryItens->whereHas('produto', function ($query) use ($termoBusca) {
                $query->where('nome', 'LIKE', "%{$termoBusca}%")
                      ->orWhere('codigo_barras', $termoBusca)
                      ->orWhere('id', $termoBusca);
            });
        }

        // Pagina os itens (filtrados ou não)
        // O ->appends($request->all()) garante que a paginação mantenha o filtro de busca
        $itens = $queryItens->paginate(50)->appends($request->all());

        // Calcula as estatísticas de progresso (a lógica não muda)
        $stats = [
            'total_items' => $inventario->items()->count(),
            'items_contados' => $inventario->items()->whereNotNull('quantidade_contada')->count(),
        ];

        return view('inventarios.contagem', compact('inventario', 'itens', 'stats'));
    }


    public function showReconciliacao(Inventario $inventario)
    {
        // Busca apenas os itens que tiveram diferença, já carregando os dados do produto.
        $itensComDiferenca = $inventario->items()
                                    ->where('diferenca', '!=', 0)
                                    ->with('produto')
                                    ->get();

        // Calcula os totais para o sumário
        $stats = [
            'total_perdas' => 0,
            'total_ganhos' => 0,
            'skus_com_diferenca' => $itensComDiferenca->count(),
        ];

        foreach ($itensComDiferenca as $item) {
            $valorDiferenca = $item->diferenca * $item->produto->preco_custo;
            if ($valorDiferenca < 0) {
                $stats['total_perdas'] += $valorDiferenca;
            } else {
                $stats['total_ganhos'] += $valorDiferenca;
            }
        }
        
        return view('inventarios.reconciliacao', compact('inventario', 'itensComDiferenca', 'stats'));
    }

    public function showVisualizacao(Inventario $inventario)
    {
        // A lógica é a mesma da reconciliação: buscar os itens com divergência
        $itensComDiferenca = $inventario->items()
                                    ->where('diferenca', '!=', 0)
                                    ->with('produto')
                                    ->get();

        // E calcular os mesmos totais
        $stats = [
            'total_perdas' => 0,
            'total_ganhos' => 0,
            'skus_com_diferenca' => $itensComDiferenca->count(),
        ];

        foreach ($itensComDiferenca as $item) {
            // Garante que o preco_custo não seja nulo para evitar erros
            $precoCusto = $item->produto->preco_custo ?? 0;
            $valorDiferenca = $item->diferenca * $precoCusto;
            if ($valorDiferenca < 0) {
                $stats['total_perdas'] += $valorDiferenca;
            } else {
                $stats['total_ganhos'] += $valorDiferenca;
            }
        }
        
        // A única diferença é que chamamos uma nova view
        return view('inventarios.visualizar', compact('inventario', 'itensComDiferenca', 'stats'));
    }


    public function marcarComoContado(Inventario $inventario)
    {
        $inventario->update(['status' => 'contado']);
        return redirect()->route('inventarios.reconciliacao', $inventario);
    }


    public function finalizar(Inventario $inventario)
    {
        $itensComDiferenca = $inventario->items()->where('diferenca', '!=', 0)->get();

        foreach ($itensComDiferenca as $item) {
            $diferenca = $item->diferenca;

            if ($diferenca > 0) { // Sobra
                EstoqueService::registrarMovimento($item->produto, 'ajuste_inventario_positivo', $diferenca, $inventario, "Ajuste de inventário #{$inventario->id}");
            } elseif ($diferenca < 0) { // Perda
                EstoqueService::registrarMovimento($item->produto, 'ajuste_inventario_negativo', abs($diferenca), $inventario, "Ajuste de inventário #{$inventario->id}");
            }
        }

        $inventario->update(['status' => 'finalizado', 'data_conclusao' => now()]);

        return redirect()->route('inventarios.index')->with('success', 'Inventário finalizado e estoque ajustado!');
    }
}