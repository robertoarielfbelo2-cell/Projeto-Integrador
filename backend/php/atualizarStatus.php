<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Verifica se existe administrador autenticado
if (!isset($_SESSION['id_administrador'])) {

    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Administrador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Recebe os dados enviados pelo JavaScript
$dados = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($dados)) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$idIndicacao = filter_var(
    $dados['id_indicacao'] ?? null,
    FILTER_VALIDATE_INT
);

$idStatus = filter_var(
    $dados['id_status'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $idIndicacao === false ||
    $idIndicacao === null ||
    $idIndicacao <= 0 ||
    $idStatus === false ||
    $idStatus === null ||
    $idStatus <= 0
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Indicação ou status inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // Confirma que o status existe
    $sqlStatus = "
        SELECT id_status
        FROM status_indicacao
        WHERE id_status = ?
    ";

    $stmtStatus = $conn->prepare($sqlStatus);
    $stmtStatus->bind_param('i', $idStatus);
    $stmtStatus->execute();

    $statusExiste = $stmtStatus
        ->get_result()
        ->fetch_assoc();

    $stmtStatus->close();

    if (!$statusExiste) {

        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Status não encontrado.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Atualiza a indicação
    $sql = "
        UPDATE indicacao
        SET
            id_status = ?,
            id_administrador = ?
        WHERE id_indicacao = ?
    ";

    $stmt = $conn->prepare($sql);

    $idAdministrador =
        (int) $_SESSION['id_administrador'];

    $stmt->bind_param(
        'iii',
        $idStatus,
        $idAdministrador,
        $idIndicacao
    );

    $stmt->execute();

    $linhasAlteradas = $stmt->affected_rows;

    $stmt->close();

    // Confirma que a indicação existe, mesmo se o status já era igual
    $stmtVerificar = $conn->prepare(
        "SELECT id_indicacao
         FROM indicacao
         WHERE id_indicacao = ?"
    );

    $stmtVerificar->bind_param('i', $idIndicacao);
    $stmtVerificar->execute();

    $indicacaoExiste = $stmtVerificar
        ->get_result()
        ->fetch_assoc();

    $stmtVerificar->close();

    if (!$indicacaoExiste) {

        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Indicação não encontrada.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Status atualizado com sucesso!',
        'alterado' => $linhasAlteradas > 0
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log(
        'Erro ao atualizar status: ' .
        $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar status.'
    ], JSON_UNESCAPED_UNICODE);

} finally {

    $conn->close();

}