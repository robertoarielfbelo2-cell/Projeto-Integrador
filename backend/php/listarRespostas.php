<?php

header('Content-Type: application/json; charset=UTF-8');

// Lista as respostas do vereador em uma indicação: ?id_indicacao=<id>
// É pública: as respostas também aparecem para o cidadão no feed (RF15).

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idIndicacao = filter_var(
    $_GET['id_indicacao'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idIndicacao === false) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Indicação inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // Da mais antiga para a mais nova: forma o histórico de acompanhamento
    $sql = "
        SELECT
            r.id_resposta,
            r.texto,
            r.data_resposta,
            TRIM(v.nome_parlamentar) AS vereador
        FROM resposta_indicacao AS r
        INNER JOIN vereador AS v
            ON r.id_vereador = v.id_vereador
        WHERE r.id_indicacao = ?
        ORDER BY r.data_resposta ASC, r.id_resposta ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idIndicacao);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $respostas = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_resposta'] = (int) $linha['id_resposta'];
        $respostas[] = $linha;
    }

    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'respostas' => $respostas
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao listar respostas: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar as respostas.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
