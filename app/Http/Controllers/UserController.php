<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role; // Importe o model de Role do Spatie
use App\Models\Empresa;
use App\Http\Controllers\EmpresaController;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carrega os usuários com seus perfis para evitar múltiplas queries (Eager Loading)
        $usuarios = User::with('roles')->latest()->paginate(15);
        return view('admin.users.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    $roles = Role::all();
    $empresas = Empresa::orderBy('razao_social')->get(); // <-- ADICIONE ESTA LINHA

    // Passe a variável $empresas para a view
    return view('admin.users.create', compact('roles', 'empresas'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'empresa_id' => ['required', 'exists:empresas,id'], // <-- VALIDAÇÃO ADICIONADA
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'empresa_id' => $request->empresa_id, // <-- CAMPO ADICIONADO
        ]);
    
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }
    
        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuário criado com sucesso.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        $empresas = Empresa::orderBy('razao_social')->get(); // <-- ADICIONAR ESTA LINHA
    
        return view('admin.users.edit', compact('usuario', 'roles', 'empresas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email,'.$usuario->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'empresa_id' => ['required', 'exists:empresas,id'], // <-- VALIDAÇÃO ADICIONADA
        ]);
    
        // Pega todos os dados validados, exceto a senha
        $data = $request->only('name', 'email', 'empresa_id'); // <-- 'empresa_id' ADICIONADO AQUI
        
        // Só atualiza a senha se ela for preenchida no formulário
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
    
        $usuario->update($data);
    
        $usuario->syncRoles($request->roles ?? []);
    
        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuário atualizado com sucesso.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        // Regra de negócio para impedir que o usuário se auto-exclua
        if (auth()->id() == $usuario->id) {
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}