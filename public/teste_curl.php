<?php

// URL do webservice de homologação (a mesma que você acessou no navegador)
$url = 'https://nfce-homologacao.svrs.rs.gov.br';

echo "<h1>Teste de Conexão cURL para a SEFAZ</h1>";
echo "<p>Tentando conectar em: <strong>" . $url . "</strong></p>";

// Inicializa o cURL
$ch = curl_init();

// Define as opções do cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o resultado como string
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Mantenha true para produção, verifica o certificado do servidor
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verifica se o nome do host no certificado corresponde
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30 segundos

// Executa a requisição
$response = curl_exec($ch);

// Verifica se ocorreu algum erro
if (curl_errno($ch)) {
    echo '<h2 style="color: red;">Erro na conexão cURL!</h2>';
    echo '<p><strong>Código do Erro:</strong> ' . curl_errno($ch) . '</p>';
    echo '<p><strong>Mensagem de Erro:</strong> ' . curl_error($ch) . '</p>';
} else {
    echo '<h2 style="color: green;">Conexão bem-sucedida!</h2>';
    echo '<p>A resposta do servidor foi recebida. O ambiente PHP consegue se comunicar com a SEFAZ.</p>';
    // Você pode descomentar a linha abaixo para ver o HTML da página que o cURL recebeu
    // echo '<hr><pre>' . htmlspecialchars($response) . '</pre>';
}

// Fecha a conexão cURL
curl_close($ch);

?>