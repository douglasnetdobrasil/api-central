@extends('layouts.app')

@section('content')
    <h3>Extrato do Centro de Custo: {{ $centroCusto->nome }}</h3>
    <p>Período de {{ \Carbon\Carbon::parse($periodo['inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($periodo['fim'])->format('d/m/Y') }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Descrição Original</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lancamentos as $lancamento)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($lancamento->lancamento->data_vencimento)->format('d/m/Y') }}</td>
                    <td>
                        @if ($lancamento->lancamento_type === 'App\Models\ContaReceber')
                            <span class="badge bg-success">Receita</span>
                        @else
                            <span class="badge bg-danger">Despesa</span>
                        @endif
                    </td>
                    <td>
                        <a href="#"> {{ $lancamento->lancamento->descricao }}
                        </a>
                    </td>
                    <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum lançamento encontrado para o período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection