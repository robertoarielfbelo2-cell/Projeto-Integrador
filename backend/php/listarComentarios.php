<?php

header('Content-Type: application/json; charset=UTF-8');

// Permite apenas GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'erro' => 'Método não permitido.'
    ]);
    exit;
}

require_once __DIR__ . '/conexao.php';

// Recebe o ID da indicação pela URL
$idIndicacao = filter_var(
    $_GET['id_indicacao'] ?? null,
    FILTER_VALIDATE_INT
);

// Verifica se o ID é válido
if ($idIndicacao === false || $idIndicacao === null || $idIndicacao <= 0) {
    http_response_code(400);

    echo json_encode([
        'erro' => 'ID da indicação inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    // Busca os comentários e os nomes dos autores
    $sql = "
        SELECT
            co.id_comentario,
            co.texto,
            co.data_comentario,
            ci.nome AS autor

        FROM comentario AS co

        INNER JOIN cidadao AS ci
            ON co.id_cidadao = ci.id_cidadao

        WHERE co.id_indicacao = ?

        ORDER BY co.data_comentario ASC, co.id_comentario ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('i', $idIndicacao);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $comentarios = [];

    while ($linha = $resultado->fetch_assoc()) {
        $comentarios[] = $linha;
    }

    echo json_encode(
        $comentarios,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    $stmt->close();

} catch (Throwable $erro) {

    error_log('Erro ao listar comentários: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível carregar os comentários.'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();