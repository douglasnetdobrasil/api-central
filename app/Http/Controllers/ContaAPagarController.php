<?php

namespace App\Http\Controllers;

use App\Models\CategoriaContaAPagar;
use App\Models\ContaAPagar;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContaAPagarController extends Controller
{
    /**
     * Exibe a lista de contas a pagar.
     */
    public function index()
    {
        $contas = ContaAPagar::with('fornecedor', 'categoriaContaAPagar.parent') // Otimização para carregar o grupo (pai)
            ->where('empresa_id', Auth::user()->empresa_id)
            ->latest('data_vencimento')
            ->paginate(15);

        return view('contas_a_pagar.index', compact('contas'));
    }

    /**
     * Mostra a view que carrega o componente Livewire para criar uma nova conta.
     */
    public function create()
    {
        // A view 'create' agora apenas carrega o componente Livewire,
        // passando uma nova instância vazia de ContaAPagar.
        return view('contas_a_pagar.create', ['conta' => new ContaAPagar()]);
    }

    /**
     * Mostra a view que carrega o componente Livewire para editar uma conta existente.
     */
    public function edit(ContaAPagar $contaAPagar)
    {
        // Validação de segurança
        if ($contaAPagar->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }

        // A view 'edit' agora apenas carrega o componente Livewire,
        // passando a conta que queremos editar.
        return view('contas_a_pagar.edit', ['conta' => $contaAPagar]);
    }

    /**
     * Remove uma conta a pagar do banco de dados.
     */
    public function destroy(ContaAPagar $contaAPagar)
    {
        // Validação de segurança
        if ($contaAPagar->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }

        // Regra de negócio: Impede a exclusão se já houver pagamentos
        // (Considerando que você tenha uma coluna 'valor_pago')
        if ($contaAPagar->valor_pago > 0) {
            return redirect()->route('contas_a_pagar.index')
                ->with('error', 'Não é possível excluir uma conta que já possui pagamentos.');
        }

        $contaAPagar->delete();

        return redirect()->route('contas_a_pagar.index')
            ->with('success', 'Conta a pagar excluída com sucesso.');
    }

    // Os métodos store() e update() foram removidos daqui
    // porque sua lógica agora reside no componente Livewire:
    // App\Livewire\ContasAPagar\ContaForm.php
}