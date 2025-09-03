<?php

namespace App\Http\Controllers;

use App\Models\Venda; // Importe o model Venda
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca os pedidos, ordenando pelos mais recentes
        // O 'with('cliente')' carrega os dados do cliente para evitar múltiplas queries
        $pedidos = Venda::with('cliente')->latest()->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }
}