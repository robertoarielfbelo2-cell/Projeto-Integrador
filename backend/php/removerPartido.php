<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Remove um partido. Somente administradores.
// Regra: só remove partido sem vereadores; antes, o admin troca o partido deles.
if (
    !isset($_SESSION['id_administrador']) ||
    !is_numeric($_SESSION['id_administrador'])
) {
    http_response_code(403);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Acesso permitido apenas para administradores."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);

$idPartido = filter_var(
    is_array($dados) ? ($dados["id"] ?? '') : '',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($idPartido === false) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Partido inválido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once 'conexao.php';

try {

    // O partido precisa existir
    $stmtPartido = $conn->prepare(
        "SELECT sigla FROM partido WHERE id_partido = ? LIMIT 1"
    );
    $stmtPartido->bind_param("i", $idPartido);
    $stmtPartido->execute();
    $partido = $stmtPartido->get_result()->fetch_assoc();
    $stmtPartido->close();

    if (!$partido) {
        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Partido não encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // Quantos vereadores ainda pertencem ao partido
    $stmtUso = $conn->prepare(
        "SELECT COUNT(*) AS total FROM vereador WHERE id_partido = ?"
    );
    $stmtUso->bind_param("i", $idPartido);
    $stmtUso->execute();
    $vereadores = (int) $stmtUso->get_result()->fetch_assoc()['total'];
    $stmtUso->close();

    if ($vereadores > 0) {
        http_response_code(409);

        $mensagem = $vereadores === 1
            ? "Não é possível remover o {$partido['sigla']}: ele tem 1 vereador. Troque o partido desse vereador antes de remover."
            : "Não é possível remover o {$partido['sigla']}: ele tem $vereadores vereadores. Troque o partido desses vereadores antes de remover.";

        echo json_encode([
            "sucesso" => false,
            "mensagem" => $mensagem
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM partido WHERE id_partido = ?");
    $stmt->bind_param("i", $idPartido);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "O partido {$partido['sigla']} foi removido."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao remover o partido."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
