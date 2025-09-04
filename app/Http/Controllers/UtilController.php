<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UtilController extends Controller
{
    public function consultarCnpj(string $cnpj)
    {
        // Limpa o CNPJ, deixando apenas números
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        // Faz a chamada para a BrasilAPI
        $response = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpjLimpo}");

        if ($response->successful()) {
            // Se a API retornou sucesso, devolve os dados em JSON
            return response()->json($response->json());
        }

        // Se a API retornou erro (ex: CNPJ não encontrado), devolve uma mensagem de erro
        return response()->json(['error' => 'CNPJ não encontrado ou inválido.'], 404);
    }
}