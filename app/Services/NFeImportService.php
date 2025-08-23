<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\ProdutoFornecedor;
use Exception;

class NFeImportService
{
    /**
     * Ponto de entrada principal. Recebe o conteúdo do XML e executa todo o fluxo.
     *
     * @param string $xmlContent
     * @return Compra
     * @throws Exception
     */
    public function processarXml(string $xmlContent): Compra
    {
        // ETAPA 1: PARSEAR O XML
        // Aqui você usaria uma biblioteca para ler o XML.
        // Vou simular os dados que viriam do XML para o exemplo.
        // Substitua esta parte pela sua biblioteca de leitura de NFe.
        $dadosNFe = $this->simularParseNFe($xmlContent);

        // ETAPA 2: VERIFICAR FORNECEDOR E NOTA
        $fornecedor = Fornecedor::firstOrCreate(
            ['cnpj' => $dadosNFe['fornecedor']['cnpj']],
            ['razao_social' => $dadosNFe['fornecedor']['razao_social']]
        );

        // Verifica se a nota já existe (como você mencionou que já faz)
        $compraExistente = Compra::where('chave_acesso_nfe', $dadosNFe['chave'])->first();
        if ($compraExistente) {
            throw new Exception("Esta nota fiscal já foi importada anteriormente (ID: {$compraExistente->id}).");
        }

        // ETAPA 3: CRIAR A COMPRA E OS ITENS (AINDA SEM VÍNCULO)
        $compra = Compra::create([
            'fornecedor_id' => $fornecedor->id,
            'numero_nota' => $dadosNFe['numero_nota'],
            'chave_acesso_nfe' => $dadosNFe['chave'],
            'valor_total_nota' => $dadosNFe['valor_total'],
            'data_emissao' => $dadosNFe['data_emissao'],
            'status' => 'Em Conferencia', // Status inicial
        ]);

        foreach ($dadosNFe['itens'] as $itemXml) {
            $compra->itens()->create([
                'descricao_item_nota' => $itemXml['nome'],
                'codigo_produto_nota' => $itemXml['codigo_fornecedor'],
                'ean_nota' => $itemXml['ean'],
                'ncm' => $itemXml['ncm'],
                'cfop' => $itemXml['cfop'],
                'quantidade' => $itemXml['quantidade'],
                'preco_custo_nota' => $itemXml['preco_custo'],
                'subtotal' => $itemXml['quantidade'] * $itemXml['preco_custo'],
            ]);
        }
        
        // ETAPA 4: A MÁGICA - VINCULAR OU CRIAR PRODUTOS AUTOMATICAMENTE
        foreach ($compra->itens as $compraItem) {
            $this->vincularOuCriarProduto($fornecedor, $compraItem);
        }

        return $compra->fresh(); // Retorna a compra com os itens atualizados
    }

    /**
     * Lógica central para encontrar um vínculo, ou criar um novo produto e o vínculo.
     */
    private function vincularOuCriarProduto(Fornecedor $fornecedor, CompraItem $compraItem): void
    {
        $produtoIdFinal = null;

        // Tenta encontrar um vínculo existente na tabela produto_fornecedores
        $vinculo = ProdutoFornecedor::where('fornecedor_id', $fornecedor->id)
                                    ->where('codigo_produto_fornecedor', $compraItem->codigo_produto_nota)
                                    ->first();

        if ($vinculo) {
            // SUCESSO! O VÍNCULO FOI ENCONTRADO
            $produto = Produto::find($vinculo->produto_id);
            if ($produto) {
                // Atualiza o custo e recalcula o preço de venda do produto existente
                $produto->preco_custo = $compraItem->preco_custo_nota;
                $produto->preco_venda = $produto->calcularPrecoVenda((float)$compraItem->preco_custo_nota);
                $produto->save();
                $produtoIdFinal = $produto->id;
            }
        } else {
            // PRODUTO NOVO! NENHUM VÍNCULO ENCONTRADO
            $novoProduto = new Produto();
            $novoProduto->nome = $compraItem->descricao_item_nota;
            $novoProduto->ativo = true;
            $novoProduto->categoria_id = null; // Categoria será definida pelo usuário na tela
            $novoProduto->preco_custo = $compraItem->preco_custo_nota;

            // Como a categoria é nula, o método usará a MARGEM PADRÃO do sistema
            $novoProduto->preco_venda = $novoProduto->calcularPrecoVenda((float)$compraItem->preco_custo_nota);
            $novoProduto->save();
            
            // "Aprende" o vínculo para a próxima vez
            ProdutoFornecedor::create([
                'produto_id' => $novoProduto->id,
                'fornecedor_id' => $fornecedor->id,
                'codigo_produto_fornecedor' => $compraItem->codigo_produto_nota,
                'ean_fornecedor' => $compraItem->ean_nota,
            ]);

            $produtoIdFinal = $novoProduto->id;
        }

        // Atualiza o item da compra com o ID do produto vinculado (seja ele novo ou antigo)
        if ($produtoIdFinal) {
            $compraItem->produto_vinculado_id = $produtoIdFinal;
            $compraItem->save();
        }
    }

    /**
     * MÉTODO SIMULADO: Substitua pela sua biblioteca de leitura de XML.
     */
    private function simularParseNFe(string $xmlContent): array
    {
        // Esta função é apenas um exemplo.
        // Os dados reais viriam da leitura do arquivo XML.
        return [
            'chave' => '33250801234567000199550010001234561000000017',
            'numero_nota' => '123456',
            'data_emissao' => now(),
            'valor_total' => 350.00,
            'fornecedor' => [
                'cnpj' => '01.234.567/0001-99',
                'razao_social' => 'Fornecedor Exemplo LTDA',
            ],
            'itens' => [
                ['nome' => 'CAMISETA GOLA V BRANCA P', 'codigo_fornecedor' => 'FORN-ABC-001', 'ean' => '7890000000011', 'ncm' => '61091000', 'cfop' => '5102', 'quantidade' => 10, 'preco_custo' => 15.00],
                ['nome' => 'CALCA JEANS SLIM M', 'codigo_fornecedor' => 'FORN-XYZ-002', 'ean' => '7890000000022', 'ncm' => '62034200', 'cfop' => '5102', 'quantidade' => 5, 'preco_custo' => 40.00],
            ]
        ];
    }
}