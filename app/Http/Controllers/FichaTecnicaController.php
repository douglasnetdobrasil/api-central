<?php

namespace App\Http\Controllers;

use App\Models\FichaTecnicaProducao;
use App\Models\Produto;
use Illuminate\Http\Request;

class FichaTecnicaController extends Controller
{
    /**
     * Mostra uma lista de produtos acabados com suas respectivas fichas técnicas.
     */
    public function index()
    {
        $produtosComFicha = Produto::where('tipo', 'produto_acabado')
                                    ->with('fichaTecnica.materiaPrima') // Carrega as receitas
                                    ->orderBy('nome')
                                    ->paginate(10);

        return view('ficha_tecnica.index', compact('produtosComFicha'));
    }

    /**
     * Mostra o formulário para criar a "cabeça" de uma nova ficha técnica
     * (apenas selecionar o produto que ainda não tem ficha).
     */
    public function create()
    {
        // Pega apenas produtos acabados que AINDA NÃO têm uma ficha técnica
        $produtosAcabados = Produto::where('tipo', 'produto_acabado')
                                    ->whereDoesntHave('fichaTecnica')
                                    ->orderBy('nome')
                                    ->get();

        return view('ficha_tecnica.create', compact('produtosAcabados'));
    }

    /**
     * Apenas cria a "cabeça" da ficha e redireciona para a tela de gestão.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produto_acabado_id' => 'required|exists:produtos,id|unique:ficha_tecnica_producao,produto_acabado_id',
        ]);

        // Não criamos nenhum item aqui, apenas redirecionamos para a tela de edição
        // passando o ID do produto. O usuário adicionará os itens lá.
        return redirect()->route('ficha-tecnica.edit', $request->produto_acabado_id);
    }
    
    /**
     * ESTA É A NOVA TELA DE GESTÃO PARA ADICIONAR VÁRIOS ITENS
     */
    public function edit($produto_id)
    {
        // 1. Buscamos manualmente o produto pelo ID.
        // O findOrFail garante que, se o produto não for encontrado, ele mostrará um erro 404 (Página não encontrada).
        $produto = Produto::findOrFail($produto_id);
    
        // 2. O resto do código continua igual.
        $produto->load('fichaTecnica.materiaPrima');
        $materiasPrimas = Produto::where('tipo', 'materia_prima')->orderBy('nome')->get();
    
        return view('ficha_tecnica.edit', compact('produto', 'materiasPrimas'));
    }

    /**
     * Adiciona UM NOVO ITEM à ficha técnica existente.
     * Esta função é chamada pelo formulário dentro da tela de 'edit'.
     */
    public function storeItem(Request $request, Produto $produto)
    {
        $request->validate([
            'materia_prima_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|numeric|min:0.0001',
        ]);

        // Checa se o item já existe para evitar duplicidade
        $exists = $produto->fichaTecnica()
                          ->where('materia_prima_id', $request->materia_prima_id)
                          ->exists();

        if ($exists) {
            return back()->with('error', 'Esta matéria-prima já foi adicionada.');
        }

        // Adiciona o novo ingrediente à receita do produto
        $produto->fichaTecnica()->create([
            'empresa_id' => auth()->user()->empresa_id,
            'materia_prima_id' => $request->materia_prima_id,
            'quantidade' => $request->quantidade,
            'observacoes' => $request->observacoes,
        ]);

        return back()->with('success', 'Ingrediente adicionado com sucesso!');
    }

    /**
     * Remove um item da ficha técnica.
     */
    public function destroy(FichaTecnicaProducao $fichaTecnica)
    {
        $fichaTecnica->delete();
        return back()->with('success', 'Ingrediente removido com sucesso!');
    }
}