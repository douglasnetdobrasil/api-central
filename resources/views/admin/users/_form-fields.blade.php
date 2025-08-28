{{-- Nome --}}
<div>
    <x-input-label for="name" value="Nome" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $usuario->name ?? '')" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

{{-- E-mail --}}
<div class="mt-4">
    <x-input-label for="email" value="E-mail" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

{{-- Senha --}}
<div class="mt-4">
    <x-input-label for="password" value="Senha" />
    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
    <x-input-error class="mt-2" :messages="$errors->get('password')" />
    @if (isset($usuario))
        <small class="text-gray-500">Deixe em branco para não alterar a senha.</small>
    @endif
</div>

{{-- Confirmação de Senha --}}
<div class="mt-4">
    <x-input-label for="password_confirmation" value="Confirmar Senha" />
    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
    <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
</div>

{{-- Perfis (Roles) --}}
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Perfis do Usuário</h3>
    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($roles as $role)
            <label class="flex items-center space-x-2">
                <input type="checkbox"
                       name="roles[]"
                       value="{{ $role->name }}"
                       class="rounded"
                       {{-- Verifica se o usuário já tem o perfil (na edição) ou se foi selecionado anteriormente (no erro de validação) --}}
                       @if( (isset($usuario) && $usuario->roles->contains($role)) || in_array($role->id, old('roles', [])) ) checked @endif
                >
                <span>{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
     <x-input-error class="mt-2" :messages="$errors->get('roles')" />
</div>

<div class="flex items-center justify-end mt-8">
    <a href="{{ route('usuarios.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
       Cancelar
   </a>
   <x-primary-button>
       Salvar
   </x-primary-button>
</div>