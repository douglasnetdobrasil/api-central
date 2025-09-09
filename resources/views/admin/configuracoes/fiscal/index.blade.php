{{-- ... layout ... --}}
<form action="{{ route('configuracoes.update') }}" method="POST">
    @csrf
    <div>
        <x-input-label for="dados_fiscais_padrao" value="Perfil Fiscal Padrão para Novos Produtos" />
        <select name="dados_fiscais_padrao" id="dados_fiscais_padrao" class="mt-1 block w-full">
            <option value="">Nenhum</option>
            @foreach ($perfisFiscais as $perfil)
                {{-- $perfilAtivo é o valor atual salvo na tabela 'configuracoes' --}}
                <option value="{{ $perfil->id }}" @selected($perfilAtivo == $perfil->id)>
                    {{ $perfil->nome_perfil }}
                </option>
            @endforeach
        </select>
        <p class="text-sm text-gray-600 mt-2">
            Selecione o conjunto de regras fiscais que será usado ao importar produtos sem vínculo.
        </p>
    </div>

    {{-- Outras configurações gerais da sua tabela 'configuracoes' --}}

    <div class="flex items-center justify-end mt-4">
        <x-primary-button>Salvar Configurações</x-primary-button>
    </div>
</form>
{{-- ... --}}