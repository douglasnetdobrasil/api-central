<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role; // Importe o model de Role do Spatie

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
        // Pega todos os perfis para poder listá-los no formulário
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
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
            'roles' => 'required|array' // Valida que pelo menos um perfil foi selecionado
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Usa o método do Spatie para atribuir os perfis
        $user->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        // Carrega os perfis que o usuário já possui
        $usuario->load('roles');
        return view('admin.users.edit', compact('usuario', 'roles'));
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
            'roles' => 'required|array'
        ]);

        $data = $request->except('password');
        
        // Só atualiza a senha se uma nova for fornecida
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);
        $usuario->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
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