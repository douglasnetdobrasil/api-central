<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoFiscalPadrao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerfilFiscalController extends Controller
{
    // Listar todos os perfis
    public function index()
    {
        $perfis = ConfiguracaoFiscalPadrao::where('empresa_id', Auth::user()->empresa_id)->paginate(10);
        return view('admin.perfis-fiscais.index', compact('perfis'));
    }

    // Mostrar formulário de criação
    public function create()
    {
        $perfil = new ConfiguracaoFiscalPadrao();
        return view('admin.perfis-fiscais.form', compact('perfil'));
    }

    // Salvar novo perfil
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['empresa_id'] = Auth::user()->empresa_id;

        ConfiguracaoFiscalPadrao::create($validated);
        return redirect()->route('admin.perfis-fiscais.index')->with('success', 'Perfil fiscal criado com sucesso!');
    }

    // Mostrar formulário de edição
    public function edit(ConfiguracaoFiscalPadrao $perfis_fiscai)
    {
        $perfil = $perfis_fiscai;

        // Verificação de segurança simples: o perfil pertence à empresa do usuário?
        if ($perfil->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }
        
        return view('admin.perfis-fiscais.form', compact('perfil'));
    }

    // Atualizar perfil existente
    public function update(Request $request, ConfiguracaoFiscalPadrao $perfis_fiscai)
    {
        $perfil = $perfis_fiscai;

        // Verificação de segurança simples
        if ($perfil->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $this->validateRequest($request);
        $perfil->update($validated);
        return redirect()->route('admin.perfis-fiscais.index')->with('success', 'Perfil fiscal atualizado com sucesso!');
    }

    // Apagar perfil
    public function destroy(ConfiguracaoFiscalPadrao $perfis_fiscai)
    {
        $perfil = $perfis_fiscai;

        // Verificação de segurança simples
        if ($perfil->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Adicionar lógica para não deixar apagar perfil em uso
        // ...

        $perfil->delete();
        return redirect()->route('admin.perfis-fiscais.index')->with('success', 'Perfil fiscal removido com sucesso!');
    }

    // Método privado para reutilizar a validação
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'nome_perfil' => 'required|string|max:255',
            'ncm_padrao' => 'nullable|string|max:10',
            'cfop_padrao' => 'nullable|string|max:4',
            'origem_padrao' => 'nullable|string|max:1',
            'csosn_padrao' => 'nullable|string|max:4',
            'icms_cst_padrao' => 'nullable|string|max:3',
            'pis_cst_padrao' => 'nullable|string|max:2',
            'cofins_cst_padrao' => 'nullable|string|max:2',
        ]);
    }
}