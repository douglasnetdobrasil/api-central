<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducao;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EstoqueMovimento;

use Exception;

class OrdemProducaoController extends Controller
{
    /**
     * Lista todas as Ordens de Produção.
     */
    public function index()
    {
        $ordensProducao = OrdemProducao::with('produtoAcabado', 'responsavel')
                                      ->latest() // Ordena pelas mais recentes
                                      ->paginate(15);

        return view('ordem_producao.index', compact('ordensProducao'));
    }

    /**
     * Mostra o formulário para criar uma nova Ordem de Produção.
     */
    public function create()
    {
        // Apenas produtos que SÃO "produto_acabado" E que POSSUEM uma ficha técnica podem ser produzidos.
        $produtosParaProduzir = Produto::where('tipo', 'produto_acabado')
                                       ->whereHas('fichaTecnica')
                                       ->orderBy('nome')
                                       ->get();

        return view('ordem_producao.create', compact('produtosParaProduzir'));
    }

    /**
     * Salva a nova Ordem de Produção e seus itens no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produto_acabado_id' => 'required|exists:produtos,id',
            'quantidade_planejada' => 'required|numeric|min:1',
            'data_inicio_prevista' => 'nullable|date',
            'data_fim_prevista' => 'nullable|date|after_or_equal:data_inicio_prevista',
        ]);

        DB::beginTransaction();
        try {
            $produto = Produto::with('fichaTecnica.materiaPrima')->findOrFail($request->produto_acabado_id);

            $ordemProducao = OrdemProducao::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'produto_acabado_id' => $produto->id,
                'quantidade_planejada' => $request->quantidade_planejada,
                'data_inicio_prevista' => $request->data_inicio_prevista,
                'data_fim_prevista' => $request->data_fim_prevista,
                'status' => 'Planejada',
            ]);

            foreach ($produto->fichaTecnica as $itemReceita) {
                // =======================================================
                // ||||||||||||||||||||| CORREÇÃO DO BUG ||||||||||||||||||||
                // =======================================================
                // Forçamos a conversão para (float) para garantir a precisão matemática
                $quantidadeCalculada = (float)$itemReceita->quantidade * (float)$ordemProducao->quantidade_planejada;

                $ordemProducao->itens()->create([
                    'materia_prima_id' => $itemReceita->materia_prima_id,
                    'quantidade_necessaria' => $quantidadeCalculada,
                    'custo_unitario_momento' => $itemReceita->materiaPrima->preco_custo ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('ordem-producao.index')->with('success', 'Ordem de Produção criada com sucesso!');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro ao criar a Ordem de Produção: ' . $e->getMessage())->withInput();
        }
    }

    public function iniciarProducao(OrdemProducao $ordemProducao)
    {
        // 1. Validação de Status
        if ($ordemProducao->status !== 'Planejada') {
            return back()->with('error', 'Esta Ordem de Produção não pode ser iniciada, pois seu status não é "Planejada".');
        }

        // Carrega os itens e as matérias-primas relacionadas
        $ordemProducao->load('itens.materiaPrima');

        // 2. Validação de Estoque (antes de iniciar a transaction)
        foreach ($ordemProducao->itens as $item) {
            if ($item->materiaPrima->estoque_atual < $item->quantidade_necessaria) {
                return back()->with('error', "Estoque insuficiente para a matéria-prima: '{$item->materiaPrima->nome}'. Necessário: {$item->quantidade_necessaria}, Disponível: {$item->materiaPrima->estoque_atual}.");
            }
        }

        DB::beginTransaction();
        try {
            // 3. Itera sobre cada matéria-prima para dar baixa no estoque
            foreach ($ordemProducao->itens as $item) {
                $materiaPrima = $item->materiaPrima;
                $quantidadeNecessaria = $item->quantidade_necessaria;
                
                // Salva o saldo anterior para o registro de movimento
                $saldoAnterior = $materiaPrima->estoque_atual;

                // Decrementa o estoque do produto (matéria-prima)
                $materiaPrima->decrement('estoque_atual', $quantidadeNecessaria);
                
                // Cria o registro de movimentação de estoque para rastreabilidade
                EstoqueMovimento::create([
                    'empresa_id' => $ordemProducao->empresa_id,
                    'produto_id' => $materiaPrima->id,
                    'user_id' => auth()->id(),
                    'tipo_movimento' => 'saida_producao',
                    'quantidade' => $quantidadeNecessaria,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_novo' => $materiaPrima->estoque_atual,
                    'origem_id' => $ordemProducao->id,
                    'origem_type' => OrdemProducao::class,
                    'observacao' => "Saída para OP #" . $ordemProducao->id,
                ]);
            }

            // 4. Atualiza o status e a data de início da Ordem de Produção
            $ordemProducao->status = 'Em Produção';
            $ordemProducao->data_inicio_real = now();
            $ordemProducao->save();

            DB::commit();

            return redirect()->route('ordem-producao.show', $ordemProducao)->with('success', 'Produção iniciada com sucesso! O estoque das matérias-primas foi atualizado.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro inesperado ao iniciar a produção: ' . $e->getMessage());
        }
    }

    public function finalizarProducao(Request $request, OrdemProducao $ordemProducao)
    {
        // 1. Validação de Status
        if ($ordemProducao->status !== 'Em Produção') {
            return back()->with('error', 'Esta Ordem de Produção não pode ser finalizada, pois seu status não é "Em Produção".');
        }

        // 2. Validação dos dados do formulário
        $validated = $request->validate([
            'quantidade_produzida' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $produtoAcabado = $ordemProducao->produtoAcabado;
            $quantidadeProduzida = (float) $validated['quantidade_produzida'];

            // Salva o saldo anterior para o registro de movimento
            $saldoAnterior = $produtoAcabado->estoque_atual;

            // Incrementa o estoque do produto acabado
            $produtoAcabado->increment('estoque_atual', $quantidadeProduzida);

            // 3. Cria o registro de movimentação de estoque para o produto final
            EstoqueMovimento::create([
                'empresa_id' => $ordemProducao->empresa_id,
                'produto_id' => $produtoAcabado->id,
                'user_id' => auth()->id(),
                'tipo_movimento' => 'entrada_producao',
                'quantidade' => $quantidadeProduzida,
                'saldo_anterior' => $saldoAnterior,
                'saldo_novo' => $produtoAcabado->estoque_atual,
                'origem_id' => $ordemProducao->id,
                'origem_type' => OrdemProducao::class,
                'observacao' => "Entrada da OP #" . $ordemProducao->id,
            ]);

            // 4. Atualiza a Ordem de Produção com os dados finais
            $ordemProducao->status = 'Concluída';
            $ordemProducao->data_fim_real = now();
            $ordemProducao->quantidade_produzida = $quantidadeProduzida;
            $ordemProducao->save();

            DB::commit();

            return redirect()->route('ordem-producao.show', $ordemProducao)->with('success', 'Produção finalizada com sucesso! O estoque do produto acabado foi atualizado.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro inesperado ao finalizar a produção: ' . $e->getMessage());
        }
    }



    /**
     * Exibe os detalhes de uma Ordem de Produção específica.
     */
    public function show(OrdemProducao $ordemProducao)
    {
        // Carrega os relacionamentos para evitar múltiplas queries na view
        $ordemProducao->load('produtoAcabado', 'responsavel', 'itens.materiaPrima');

        return view('ordem_producao.show', compact('ordemProducao'));
    }

    public function destroy(OrdemProducao $ordemProducao)
    {
        // Regra de segurança: Só permite excluir se o status for 'Planejada'
        if ($ordemProducao->status !== 'Planejada') {
            return redirect()->route('ordem-producao.index')->with('error', 'Apenas Ordens de Produção com status "Planejada" podem ser excluídas.');
        }

        try {
            $ordemProducao->delete();
            return redirect()->route('ordem-producao.index')->with('success', 'Ordem de Produção excluída com sucesso!');
        } catch (Exception $e) {
            return redirect()->route('ordem-producao.index')->with('error', 'Ocorreu um erro ao excluir a Ordem de Produção.');
        }
    }
}