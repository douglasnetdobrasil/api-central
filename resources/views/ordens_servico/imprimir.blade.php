<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #{{ $ordemServico->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; font-size: 12px; color: #333; }
        .container { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .header .empresa-info h1 { margin: 0; font-size: 18px; }
        .header .empresa-info p { margin: 2px 0; }
        .header .os-info { text-align: right; }
        .header .os-info h2 { margin: 0; font-size: 16px; color: #555; }
        .section { margin-top: 20px; border: 1px solid #ddd; border-radius: 5px; padding: 15px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .grid .item p { margin: 3px 0; }
        .grid .item strong { display: block; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        thead { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .footer { margin-top: 40px; }
        .assinatura { margin-top: 80px; text-align: center; }
        .assinatura p { margin: 0; }
        .no-print { margin-top: 20px; text-align: center; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <header class="header">
            <div class="empresa-info">
                <h1>{{ $empresa->razao_social ?? 'Nome da Empresa' }}</h1>
                <p>{{ $empresa->logradouro ?? '' }}, {{ $empresa->numero ?? '' }} - {{ $empresa->bairro ?? '' }}</p>
                <p>{{ $empresa->municipio ?? '' }} - {{ $empresa->uf ?? '' }} | CEP: {{ $empresa->cep ?? '' }}</p>
                <p>CNPJ: {{ $empresa->cnpj ?? '' }}</p>
                <p>Telefone: {{ $empresa->telefone ?? '' }}</p>
            </div>
            <div class="os-info">
                <h2>ORDEM DE SERVIÇO</h2>
                <p><strong>Nº:</strong> {{ $ordemServico->id }}</p>
                <p><strong>Data de Entrada:</strong> {{ $ordemServico->data_entrada->format('d/m/Y H:i') }}</p>
                <p><strong>Status:</strong> {{ $ordemServico->status }}</p>
            </div>
        </header>

        <div class="section">
            <div class="section-title">Informações do Cliente</div>
            <p><strong>Nome:</strong> {{ $ordemServico->cliente->nome }}</p>
            <p><strong>Telefone:</strong> {{ $ordemServico->cliente->telefone ?? 'Não informado' }}</p>
            <p><strong>Email:</strong> {{ $ordemServico->cliente->email ?? 'Não informado' }}</p>
        </div>

        <div class="section">
            <div class="section-title">Informações do Equipamento</div>
            <div class="grid">
                <div class="item">
                    <strong>Equipamento:</strong>
                    <p>{{ $ordemServico->equipamento }}</p>
                </div>
                <div class="item">
                    <strong>Nº de Série:</strong>
                    <p>{{ $ordemServico->numero_serie ?? 'Não informado' }}</p>
                </div>
                <div class="item" style="grid-column: span 2;">
                    <strong>Defeito Relatado:</strong>
                    <p>{{ $ordemServico->defeito_relatado }}</p>
                </div>
                <div class="item" style="grid-column: span 2;">
                    <strong>Laudo Técnico:</strong>
                    <p>{{ $ordemServico->laudo_tecnico ?? 'Ainda não preenchido.' }}</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Peças e Produtos Utilizados</div>
            <table>
                <thead><tr><th>Produto</th><th>Qtd.</th><th class="text-right">Vlr. Unit.</th><th class="text-right">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($ordemServico->produtos as $item)
                    <tr>
                        <td>{{ $item->produto->nome }}</td>
                        <td>{{ (float)$item->quantidade }}</td>
                        <td class="text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">Nenhuma peça utilizada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Serviços Prestados</div>
            <table>
                <thead><tr><th>Serviço (Executado por)</th><th>Qtd/Horas</th><th class="text-right">Vlr. Unit.</th><th class="text-right">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($ordemServico->servicos as $item)
                    <tr>
                        <td>{{ $item->servico->nome }} @if($item->tecnico) <small> (Téc: {{ $item->tecnico->name }})</small> @endif</td>
                        <td>{{ (float)$item->quantidade }}</td>
                        <td class="text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">Nenhum serviço prestado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="section footer">
            <div class="grid">
                <div></div>
                <div class="item text-right">
                    <strong>Total Peças:</strong> R$ {{ number_format($ordemServico->valor_produtos, 2, ',', '.') }} <br>
                    <strong>Total Serviços:</strong> R$ {{ number_format($ordemServico->valor_servicos, 2, ',', '.') }} <br>
                    <strong>Desconto:</strong> R$ {{ number_format($ordemServico->valor_desconto, 2, ',', '.') }} <br>
                    <strong style="font-size: 14px;">Total Geral: R$ {{ number_format($ordemServico->valor_total, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="assinatura">
            <p>_________________________________________________</p>
            <p>Assinatura do Cliente</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()">Imprimir</button>
            <a href="{{ route('ordens-servico.edit', $ordemServico->id) }}">Voltar</a>
        </div>
    </div>

</body>
</html>