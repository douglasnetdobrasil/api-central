<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\ContaAReceber;
use App\Models\Cliente; // Para o filtro
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CobrancaController extends Controller
{
    public function index(Request $request)
    {
        $query = ContaAReceber::with('cliente', 'venda') // Carrega relacionamentos
                        ->latest('data_vencimento'); // Ordena pelas mais próximas do vencimento

        // --- FILTROS ---
        $clienteId = $request->input('cliente_id');
        $status = $request->input('status'); // Agora pode ser 'Vencido'
        $dataVencInicio = $request->input('venc_inicio');
        $dataVencFim = $request->input('venc_fim');

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        // --- LÓGICA DO FILTRO DE STATUS (MODIFICADA) ---
        if ($status) {
            if ($status == 'Vencido') {
                // Se o filtro for 'Vencido', busca 'A Receber' ou 'Parcialmente' E data < hoje
                $query->whereIn('status', ['A Receber', 'Recebido Parcialmente'])
                      ->whereDate('data_vencimento', '<', Carbon::today());
            } else {
                // Para outros status, filtra normalmente
                $query->where('status', $status);
            }
        }
        // --- FIM DA MODIFICAÇÃO ---

        if ($dataVencInicio) {
            $query->whereDate('data_vencimento', '>=', $dataVencInicio);
        }
        if ($dataVencFim) {
            $query->whereDate('data_vencimento', '<=', $dataVencFim);
        }

        // --- KPIs ---
        // Clona a query ANTES da paginação para calcular totais gerais
        $queryTotal = ContaAReceber::query(); // Começa uma nova query para totais gerais (sem filtros de paginação)
         if ($clienteId) $queryTotal->where('cliente_id', $clienteId);
         // Aplica filtros de data se existirem para os KPIs também
         if ($dataVencInicio) $queryTotal->whereDate('data_vencimento', '>=', $dataVencInicio);
         if ($dataVencFim) $queryTotal->whereDate('data_vencimento', '<=', $dataVencFim);
        
        $totalAReceber = (clone $queryTotal)->whereIn('status', ['A Receber', 'Recebido Parcialmente'])->sum(DB::raw('valor - valor_recebido'));
        $totalVencido = (clone $queryTotal)->whereIn('status', ['A Receber', 'Recebido Parcialmente'])
                                   ->where('data_vencimento', '<', Carbon::today()->toDateString())
                                   ->sum(DB::raw('valor - valor_recebido'));
        $totalRecebidoPeriodo = (clone $queryTotal)->where('status', 'Recebido')
                                         ->sum('valor_recebido');


        // Paginação
        $contasAReceber = $query->paginate(20)->withQueryString();

        // Dados para os filtros da view
        $clientes = Cliente::orderBy('nome')->pluck('nome', 'id');
        // --- ADICIONADO 'Vencido' ÀS OPÇÕES ---
        $statusOptions = ['A Receber', 'Recebido Parcialmente', 'Recebido', 'Cancelado', 'Vencido'];

        return view('financeiro.cobrancas.index', compact(
            'contasAReceber',
            'clientes',
            'statusOptions', // Agora inclui 'Vencido'
            'totalAReceber',
            'totalVencido',
            'totalRecebidoPeriodo',
            // Envia os valores dos filtros de volta para preencher os campos
            'clienteId', 'status', 'dataVencInicio', 'dataVencFim'
        ));
    }

    public function registrarRecebimento(Request $request, ContaAReceber $conta)
    {
        // Validação básica
        $validated = $request->validate([
            'valor_pago' => 'required|numeric|min:0.01',
            'data_pagamento' => 'required|date',
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
        ]);

        $valorPago = (float) $validated['valor_pago'];
        $valorPendenteAnterior = $conta->valor - $conta->valor_recebido;

        // Não permite pagar mais do que o pendente (ajuste se permitir crédito)
        if ($valorPago > $valorPendenteAnterior) {
            return redirect()->back()->with('error', 'O valor pago não pode ser maior que o valor pendente.');
        }

        try {
            DB::transaction(function () use ($conta, $valorPago, $validated, $valorPendenteAnterior) {
                // 1. Cria o registro do recebimento
                $conta->recebimentos()->create([
                    'empresa_id' => $conta->empresa_id, // Ou Auth::user()->empresa_id
                    'forma_pagamento_id' => $validated['forma_pagamento_id'],
                    'valor_recebido' => $valorPago,
                    'data_recebimento' => $validated['data_pagamento'],
                    // Adicionar juros, multa, desconto se necessário
                ]);

                // 2. Atualiza a conta a receber
                $novoValorRecebido = $conta->valor_recebido + $valorPago;
                $novoStatus = $conta->status;

                // Define o novo status
                if ($novoValorRecebido >= $conta->valor) {
                    $novoStatus = 'Recebido';
                } elseif ($novoValorRecebido > 0) {
                    $novoStatus = 'Recebido Parcialmente';
                }

                $conta->update([
                    'valor_recebido' => $novoValorRecebido,
                    'status' => $novoStatus
                ]);
            });

            return redirect()->route('financeiro.cobrancas.index')->with('success', 'Pagamento registrado com sucesso!');

        } catch (\Exception $e) {
            // Log::error('Erro ao registrar pagamento: ' . $e->getMessage()); // Opcional: Logar erro
            return redirect()->back()->with('error', 'Erro ao registrar pagamento: ' . $e->getMessage());
        }
    }

    public function gerarPdfFatura(ContaAReceber $conta)
    {
        // 1. Instalar o DomPDF: composer require barryvdh/laravel-dompdf
        // 2. Carregar dados necessários (venda, itens, cliente, empresa)
        $conta->load('cliente', 'venda.items.produto', 'venda.empresa');

        if (!$conta->venda) {
            return redirect()->back()->with('error', 'Não é possível gerar PDF para contas sem venda associada.');
        }

        // 3. Renderizar uma view Blade específica para o PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('financeiro.cobrancas.pdf_fatura', compact('conta'));

        // 4. Exibir ou baixar o PDF
        return $pdf->stream('fatura_' . $conta->id . '_' . $conta->cliente->nome . '.pdf');
        // Para baixar: return $pdf->download(...);
    }

    public function enviarEmailCobranca(Request $request, ContaAReceber $conta)
    {
        // 1. Certifique-se que suas configurações de email (.env) estão corretas
        // 2. Crie um Mailable: php artisan make:mail CobrancaEmail
        // 3. Implemente a lógica no Mailable para anexar o PDF gerado

        $conta->load('cliente', 'venda.items.produto', 'venda.empresa');
        if (!$conta->cliente || !$conta->cliente->email) {
            return redirect()->back()->with('error', 'Cliente sem email cadastrado.');
        }

        try {
             // Gera o PDF em memória (mesma lógica do gerarPdfFatura, mas sem stream/download)
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('financeiro.cobrancas.pdf_fatura', compact('conta'));
            $pdfContent = $pdf->output(); // Pega o conteúdo binário do PDF

             // Envia o email usando o Mailable
             \Illuminate\Support\Facades\Mail::to($conta->cliente->email)
                 ->send(new \App\Mail\CobrancaEmail($conta, $pdfContent)); // Passe a conta e o conteúdo do PDF

             return redirect()->back()->with('success', 'Email de cobrança enviado para ' . $conta->cliente->email);

         } catch (\Exception $e) {
             // Log::error('Erro ao enviar email de cobrança: ' . $e->getMessage());
             return redirect()->back()->with('error', 'Erro ao enviar email: ' . $e->getMessage());
         }
    }

    // --- Outros métodos (gerarPdfFatura, registrarRecebimento, etc.) virão aqui ---

}
