<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use Illuminate\Http\Request;

class VendaController extends Controller
{
    /**
     * Exibe os detalhes completos de uma venda.
     */

    public function index(Request $request)
    {
        // Inicia a query com os relacionamentos que vamos precisar
        $query = Venda::with(['cliente', 'nfes'])->latest();
    
        // Verifica se os campos de busca foram preenchidos
        if ($request->filled('search') && $request->filled('filter_by')) {
            $search = $request->search;
            $filter = $request->filter_by;
    
            switch ($filter) {
                case 'cliente':
                    // Busca no nome do cliente relacionado
                    $query->whereHas('cliente', function ($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%");
                    });
                    break;
                case 'nfce':
                    // Busca no número da nota fiscal (modelo 65 = NFC-e)
                    $query->whereHas('nfes', function ($q) use ($search) {
                        $q->where('modelo', '65')->where('numero_nfe', $search);
                    });
                    break;
                case 'nfe':
                    // Busca no número da nota fiscal (modelo 55 = NF-e)
                    $query->whereHas('nfes', function ($q) use ($search) {
                        $q->where('modelo', '55')->where('numero_nfe', $search);
                    });
                    break;
                case 'venda_id':
                    // Busca diretamente pelo ID da Venda
                    $query->where('id', $search);
                    break;
            }
        }
    
        // Executa a query com paginação e mantém os filtros na URL
        $vendas = $query->paginate(20)->withQueryString();
    
        return view('vendas.index', compact('vendas'));
    }
    public function show(Venda $venda)
    {
        // Carrega todos os relacionamentos necessários de uma só vez para otimizar a performance
        $venda->load('cliente', 'items.produto', 'nfes', 'movimentosEstoque');

        return view('vendas.show', compact('venda'));
    }
}