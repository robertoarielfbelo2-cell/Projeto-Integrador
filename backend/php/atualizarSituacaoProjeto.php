<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// O vereador atualiza a situação de tramitação de um projeto de lei DELE.

if (!isset($_SESSION['id_vereador'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Vereador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idVereador = (int) $_SESSION['id_vereador'];

$dados = json_decode(file_get_contents('php://input'), true);

$idProjeto = filter_var(
    is_array($dados) ? ($dados['id_projeto'] ?? '') : '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$idSituacao = filter_var(
    is_array($dados) ? ($dados['id_situacao'] ?? '') : '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idProjeto === false || $idSituacao === false) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Projeto ou situação inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // A situação precisa existir
    $stmtSituacao = $conn->prepare(
        'SELECT 1 FROM situacao_projeto WHERE id_situacao = ? LIMIT 1'
    );
    $stmtSituacao->bind_param('i', $idSituacao);
    $stmtSituacao->execute();
    $stmtSituacao->store_result();
    $situacaoExiste = $stmtSituacao->num_rows > 0;
    $stmtSituacao->close();

    if (!$situacaoExiste) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Situação não encontrada.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // O projeto precisa existir e ser do vereador da sessão
    $stmtProjeto = $conn->prepare(
        'SELECT id_vereador FROM projeto_lei WHERE id_projeto = ? LIMIT 1'
    );
    $stmtProjeto->bind_param('i', $idProjeto);
    $stmtProjeto->execute();
    $projeto = $stmtProjeto->get_result()->fetch_assoc();
    $stmtProjeto->close();

    if (!$projeto) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Projeto de lei não encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ((int) $projeto['id_vereador'] !== $idVereador) {
        http_response_code(403);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Este projeto de lei não é seu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $conn->prepare(
        'UPDATE projeto_lei SET id_situacao = ? WHERE id_projeto = ? AND id_vereador = ?'
    );
    $stmt->bind_param('iii', $idSituacao, $idProjeto, $idVereador);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Situação atualizada com sucesso!'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao atualizar situação do projeto: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar a situação.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
