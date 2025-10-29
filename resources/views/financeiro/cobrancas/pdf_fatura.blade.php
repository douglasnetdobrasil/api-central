<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fatura #{{ $conta->venda->id ?? $conta->id }}</title>
    <style> /* Estilos básicos para o PDF */ body { font-family: sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ccc; padding: 5px; } </style>
</head>
<body>
    <h1>Fatura / Cobrança</h1>
    <p><strong>Empresa:</strong> {{ $conta->venda->empresa->razao_social ?? 'N/A' }}</p>
    <p><strong>Cliente:</strong> {{ $conta->cliente->nome ?? 'N/A' }}</p>
    <p><strong>Vencimento:</strong> {{ $conta->data_vencimento->format('d/m/Y') }}</p>
    <p><strong>Valor:</strong> R$ {{ number_format($conta->valor, 2, ',', '.') }}</p>
    <hr>
    @if ($conta->venda)
        <h2>Detalhes da Venda #{{ $conta->venda->id }}</h2>
        <table>
            <thead><tr><th>Produto/Serviço</th><th>Qtd</th><th>Vlr Unit.</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach($conta->venda->items as $item)
                <tr>
                    <td>{{ $item->descricao_produto }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p><strong>Total Venda: R$ {{ number_format($conta->venda->total, 2, ',', '.') }}</strong></p>
    @else
        <p><strong>Descrição:</strong> {{ $conta->descricao }}</p>
    @endif
    {{-- Adicionar informações de pagamento (boleto, PIX, etc.) aqui --}}
</body>
</html>