<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $perfis = Role::paginate(10);
        return view('admin.perfis.index', compact('perfis'));
    }

    public function create()
    {
        return view('admin.perfis.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);
        return redirect()->route('perfis.index')->with('success', 'Perfil criado com sucesso.');
    }

    public function edit(Role $perfi)
    {
        $permissions = Permission::all();
        $perfi->load('permissions'); // Carrega as permissões do perfil
        return view('admin.perfis.edit', compact('perfi', 'permissions'));
    }

    public function update(Request $request, Role $perfi)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $perfi->id,
            'permissions' => 'nullable|array'
        ]);

        $perfi->update(['name' => $request->name]);

        // Método do pacote Spatie para sincronizar as permissões
        $perfi->syncPermissions($request->permissions ?? []);

        return redirect()->route('perfis.index')->with('success', 'Perfil atualizado com sucesso!');
    }

    public function destroy(Role $perfi)
    {
        $perfi->delete();
        return redirect()->route('perfis.index')->with('success', 'Perfil excluído com sucesso.');
    }
}