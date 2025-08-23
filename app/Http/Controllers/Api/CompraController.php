<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraController extends Controller
{
    /**
     * Armazena uma nova pré-nota de compra.
     */
    public function store(StoreCompraRequest $request)
    {
        $dadosValidados = $request->validated();

        try {
            $compra = DB::transaction(function () use ($dadosValidados) {
                // 1. Cria o cabeçalho da compra com status inicial 'digitacao'
                $novaCompra = Compra::create([
                    'empresa_id' => auth()->user()->empresa_id, // Pega a empresa do usuário logado
                    'fornecedor_id' => $dadosValidados['fornecedor_id'],
                    'numero_nota' => $dadosValidados['numero_nota'],
                    'serie_nota' => $dadosValidados['serie_nota'] ?? null,
                    'data_emissao' => $dadosValidados['data_emissao'],
                    'valor_total_nota' => $dadosValidados['valor_total_nota'],
                    'observacoes' => $dadosValidados['observacoes'] ?? null,
                    'status' => 'digitacao',
                ]);

                // 2. Cria os itens da compra, associando-os à nova compra
                $novaCompra->itens()->createMany($dadosValidados['itens']);

                return $novaCompra;
            });

            // Retorna a compra criada com seus itens
            return response()->json($compra->load('itens', 'fornecedor'), 201);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar pré-nota de compra: ', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ocorreu um erro inesperado ao registrar a pré-nota.'], 500);
        }
    }

    // ... outros métodos (index, show, update, destroy) virão depois
}