<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Transportadora: {{ $transportadora->razao_social }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('transportadoras.update', $transportadora) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('transportadoras._form')
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('buscar-cnpj-btn').addEventListener('click', function() {
        const cnpj = document.getElementById('cnpj').value.replace(/\D/g, '');
        const statusDiv = document.getElementById('cnpj-status');

        if (cnpj.length !== 14) {
            statusDiv.textContent = 'Por favor, digite um CNPJ válido com 14 dígitos.';
            statusDiv.style.color = 'red';
            return;
        }

        statusDiv.textContent = 'Buscando...';
        statusDiv.style.color = 'orange';
        this.disabled = true;

        fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`)
            .then(response => {
                if (!response.ok) throw new Error('CNPJ não encontrado ou inválido.');
                return response.json();
            })
            .then(data => {
                document.getElementById('razao_social').value = data.razao_social || '';
                document.getElementById('nome_fantasia').value = data.nome_fantasia || '';
                document.getElementById('cep').value = data.cep || '';
                document.getElementById('logradouro').value = data.logradouro || '';
                document.getElementById('numero').value = data.numero || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.municipio || '';
                document.getElementById('uf').value = data.uf || '';
                document.getElementById('telefone').value = data.ddd_telefone_1 || '';
                document.getElementById('email').value = data.email || '';
                statusDiv.textContent = 'Dados preenchidos!';
                statusDiv.style.color = 'green';
            })
            .catch(error => {
                statusDiv.textContent = error.message;
                statusDiv.style.color = 'red';
            })
            .finally(() => {
                this.disabled = false;
            });
    });
});
    </script>
   
</x-app-layout>