<x-mail::message>
# Olá {{ $conta->cliente->nome ?? 'Cliente' }},

Segue em anexo a fatura referente aos nossos serviços/produtos.

**Vencimento:** {{ $conta->data_vencimento->format('d/m/Y') }} <br>
**Valor:** R$ {{ number_format($conta->valor, 2, ',', '.') }}

{{-- Adicionar link para pagamento online ou instruções aqui --}}

<x-mail::button :url="$contaUrl">
Acessar Portal (Se aplicável)
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>