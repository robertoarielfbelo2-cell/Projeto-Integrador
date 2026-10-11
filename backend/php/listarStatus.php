<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

if (!isset($_SESSION['id_administrador'])) {

    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Acesso não autorizado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    $sql = "
        SELECT
            id_status,
            nome_do_status
        FROM status_indicacao
        ORDER BY id_status ASC
    ";

    $resultado = $conn->query($sql);

    $status = [];

    while ($linha = $resultado->fetch_assoc()) {

        $linha['id_status'] =
            (int) $linha['id_status'];

        $status[] = $linha;
    }

    echo json_encode([
        'sucesso' => true,
        'status' => $status
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log(
        'Erro ao listar status: ' .
        $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao consultar status.'
    ], JSON_UNESCAPED_UNICODE);

} finally {

    $conn->close();
}