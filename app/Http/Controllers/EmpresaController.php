<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Essencial para pegar o usuário logado
use Illuminate\Support\Facades\Storage;
use App\Models\Empresa; // Garanta que o modelo Empresa seja importado

class EmpresaController extends Controller
{
    /**
     * Mostra o formulário para editar os dados da empresa do usuário autenticado.
     */


    public function index()
    {
        $empresas = Empresa::latest()->paginate(10); // Busca todas as empresas
        return view('admin.empresa.index', compact('empresas'));
    }
    public function edit()
    {
        // Pega a empresa que está ligada ao usuário atualmente logado
        $empresa = Auth::user()->empresa;

        // Se, por algum motivo, o usuário não tiver uma empresa vinculada, exibe um erro.
        if (!$empresa) {
            // Você pode personalizar essa mensagem ou redirecionar para uma página de erro
            abort(403, 'Nenhuma empresa associada a este usuário.');
        }

        // A variável $empresa será enviada para a view
        return view('admin.empresa.edit', compact('empresa'));
    }

    /**
     * Atualiza os dados da empresa no banco de dados.
     */
    public function update(Request $request)
    {
        // Pega a empresa do usuário logado para garantir que ele só edite a própria empresa
        $empresa = Auth::user()->empresa;

        $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            // Valida o CNPJ para ser único, mas ignora o CNPJ da empresa que já está sendo editada
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validação para o upload do logo
        ]);

        $data = $request->except('logo');

        // Lógica para salvar o logo
        if ($request->hasFile('logo')) {
            // Se já existe um logo antigo, apaga ele do armazenamento
            if ($empresa->logo_path && Storage::disk('public')->exists($empresa->logo_path)) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            // Salva o novo logo na pasta 'public/logos' e guarda o caminho no banco
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $empresa->update($data);

        return redirect()->route('empresa.edit')->with('success', 'Dados da empresa salvos com sucesso!');
    }


    public function create()
    {
        // Passa uma nova instância vazia para o formulário
        $empresa = new Empresa();
        return view('admin.empresa.create', compact('empresa'));
    }

    /**
     * Salva a nova empresa no banco de dados.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj',
            'nicho_negocio' => 'required|in:mercado,oficina,restaurante,loja_roupas',
        ]);

        Empresa::create($validatedData);

        return redirect()->route('empresa.index')->with('success', 'Empresa cadastrada com sucesso!');
    }


    public function editAdmin(Empresa $empresa)
    {
        return view('admin.empresa.edit', compact('empresa'));
    }

    /**
     * (ADMIN) Atualiza os dados de uma empresa específica.
     */
    public function updateAdmin(Request $request, Empresa $empresa)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'nicho_negocio' => 'required|in:mercado,oficina,restaurante,loja_roupas',
        ]);
        
        $empresa->update($validatedData);
        return redirect()->route('empresa.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * (ADMIN) Exclui uma empresa.
     */
    public function destroy(Empresa $empresa)
    {
        if ($empresa->usuarios()->exists()) {
            return back()->with('error', 'Não é possível excluir uma empresa que possui usuários vinculados.');
        }

        $empresa->delete();
        return redirect()->route('empresa.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
