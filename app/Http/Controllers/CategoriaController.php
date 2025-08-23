<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca todas as categorias, ordenadas pela mais recente, com paginação
        $categorias = Categoria::latest()->paginate(10);
        return view('categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Apenas retorna a view do formulário de criação
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados do formulário
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:categorias',
            'margem_lucro' => 'nullable|numeric|min:0',
        ]);

        // Cria a nova categoria no banco de dados
        Categoria::create($validated);

        // Redireciona para a lista de categorias com uma mensagem de sucesso
        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        // Retorna a view de edição, passando a categoria que queremos editar
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        // Validação dos dados (unique é ignorado para o id da categoria atual)
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $categoria->id,
            'margem_lucro' => 'nullable|numeric|min:0',
        ]);

        // Atualiza a categoria no banco de dados
        $categoria->update($validated);

        // Redireciona com mensagem de sucesso
        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        // Lógica para verificar se a categoria está em uso (opcional mas recomendado)
        if ($categoria->produtos()->count() > 0) {
            return redirect()->route('categorias.index')->with('error', 'Esta categoria não pode ser excluída pois está vinculada a produtos.');
        }

        // Deleta a categoria
        $categoria->delete();

        // Redireciona com mensagem de sucesso
        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}