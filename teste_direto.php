<?php
date_default_timezone_set('America/Sao_Paulo');
require __DIR__ . '/vendor/autoload.php';

// ===== ENDEREÇOS CORRETOS E DEFINITIVOS =====
use NFePHP\NFe\Common\Config;
use NFePHP\NFe\Make;
// ============================================

echo "======================================================================\n";
echo "==  INICIANDO TESTE FINAL E DEFINITIVO                            ==\n";
echo "======================================================================\n\n";

try {
    echo "1. Testando a classe 'Config'...\n";
    $configJson = json_encode([
        'atualizacao' => date('Y-m-d H:i:s'),
        'tpAmb' => 2, 'razaosocial' => 'Empresa Teste',
        'siglaUF' => 'RJ', 'cnpj' => '00000000000191',
        'schemes' => 'PL_009_v4', 'versao' => '4.00'
    ]);
    $config = new Config($configJson);
    echo "   -> SUCESSO: Classe 'Config' (NFePHP\NFe\Common\Config) carregada!\n\n";

    echo "2. Testando a classe 'Make'...\n";
    $nfe = new Make();
    echo "   -> SUCESSO: Classe 'Make' (NFePHP\NFe\Make) carregada!\n\n";

    echo "----------------------------------------------------------------------\n";
    echo "RESULTADO: SUCESSO TOTAL! A biblioteca está instalada e acessível.\n";
    echo "----------------------------------------------------------------------\n";

} catch (Throwable $e) {
    echo "\n----------------------------------------------------------------------\n";
    echo "RESULTADO: FALHA!\n\n";
    echo "Ocorreu um erro: " . $e->getMessage() . "\n";
    echo "No arquivo: " . $e->getFile() . " (Linha: " . $e->getLine() . ")\n";
    echo "----------------------------------------------------------------------\n";
}