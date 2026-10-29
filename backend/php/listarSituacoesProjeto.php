<?php

header('Content-Type: application/json; charset=UTF-8');

// Lista as situações de tramitação dos projetos de lei.
// É pública: o cidadão também usa para filtrar os projetos.

require_once __DIR__ . '/conexao.php';

try {

    $resultado = $conn->query("
        SELECT id_situacao, nome_da_situacao
        FROM situacao_projeto
        ORDER BY id_situacao ASC
    ");

    $situacoes = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_situacao'] = (int) $linha['id_situacao'];
        $situacoes[] = $linha;
    }

    echo json_encode([
        'sucesso' => true,
        'situacoes' => $situacoes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao listar situações dos projetos: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar as situações.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
