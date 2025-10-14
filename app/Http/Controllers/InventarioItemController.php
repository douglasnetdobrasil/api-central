<?php

namespace App\Http\Controllers;

use App\Models\InventarioItem;
use Illuminate\Http\Request;

class InventarioItemController extends Controller
{
    public function update(Request $request, InventarioItem $inventarioItem)
    {
        $dadosValidados = $request->validate([
            'quantidade_contada' => 'required|numeric|min:0',
        ]);

        $quantidadeContada = (float) $dadosValidados['quantidade_contada'];

        $inventarioItem->update([
            'quantidade_contada' => $quantidadeContada,
            'diferenca' => $quantidadeContada - $inventarioItem->estoque_esperado,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contagem salva!',
            'diferenca' => $inventarioItem->diferenca,
        ]);
    }
}