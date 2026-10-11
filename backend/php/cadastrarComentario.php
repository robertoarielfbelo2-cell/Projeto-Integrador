<?php

header('Content-Type: application/json; charset=UTF-8');

// 1. Permite apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

// 2. Verifica se o cidadão está logado
session_start();

if (!isset($_SESSION['id_cidadao'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para comentar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ID do cidadão vem da sessão
$idCidadao = (int) $_SESSION['id_cidadao'];

// 3. Recebe os dados enviados pelo JavaScript
$dados = json_decode(
    file_get_contents('php://input'),
    true
);

$idIndicacao = filter_var(
    $dados['id_indicacao'] ?? null,
    FILTER_VALIDATE_INT
);

$texto = trim($dados['texto'] ?? '');

// 4. Valida o ID da indicação
if (
    $idIndicacao === false ||
    $idIndicacao === null ||
    $idIndicacao <= 0
) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Indicação inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. Valida o comentário
if ($texto === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O comentário não pode estar vazio.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($texto, 'UTF-8') > 500) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O comentário deve ter no máximo 500 caracteres.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // 6. Verifica se a indicação existe
    $sqlVerificar = "
        SELECT id_indicacao
        FROM indicacao
        WHERE id_indicacao = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sqlVerificar);

    $stmt->bind_param('i', $idIndicacao);

    $stmt->execute();
    $stmt->store_result();

    $indicacaoExiste = $stmt->num_rows > 0;

    $stmt->close();

    if (!$indicacaoExiste) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Indicação não encontrada.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // 7. Insere o comentário no banco
    $sqlComentario = "
        INSERT INTO comentario (
            texto,
            id_cidadao,
            id_indicacao
        )
        VALUES (?, ?, ?)
    ";

    $stmt = $conn->prepare($sqlComentario);

    $stmt->bind_param(
        'sii',
        $texto,
        $idCidadao,
        $idIndicacao
    );

    $stmt->execute();

    $idComentario = $conn->insert_id;

    $stmt->close();

    // 8. Retorna sucesso ao JavaScript
    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Comentário cadastrado com sucesso!',
        'id_comentario' => $idComentario
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log(
        'Erro ao cadastrar comentário: ' .
        $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível cadastrar o comentário.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}