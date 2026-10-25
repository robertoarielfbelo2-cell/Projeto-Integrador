<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Remove o registro de um vereador (RF09). Somente administradores.
// Regra: só remove quem não tem indicações encaminhadas nem respostas publicadas,
// para não apagar o histórico que os cidadãos já viram.
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

$idVereador = filter_var(
    is_array($dados) ? ($dados["id"] ?? '') : '',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($idVereador === false) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Vereador inválido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once 'conexao.php';

try {

    // O vereador precisa existir
    $stmtVereador = $conn->prepare(
        "SELECT TRIM(nome_parlamentar) AS nome FROM vereador WHERE id_vereador = ? LIMIT 1"
    );
    $stmtVereador->bind_param("i", $idVereador);
    $stmtVereador->execute();
    $vereador = $stmtVereador->get_result()->fetch_assoc();
    $stmtVereador->close();

    if (!$vereador) {
        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Vereador não encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // Conta o que está ligado ao vereador
    $stmtUso = $conn->prepare("
        SELECT
            (SELECT COUNT(*) FROM indicacao WHERE id_vereador = ?) AS indicacoes,
            (SELECT COUNT(*) FROM resposta_indicacao WHERE id_vereador = ?) AS respostas
    ");
    $stmtUso->bind_param("ii", $idVereador, $idVereador);
    $stmtUso->execute();
    $uso = $stmtUso->get_result()->fetch_assoc();
    $stmtUso->close();

    $indicacoes = (int) $uso['indicacoes'];
    $respostas = (int) $uso['respostas'];

    if ($indicacoes > 0 || $respostas > 0) {

        $motivos = [];

        if ($indicacoes > 0) {
            $motivos[] = $indicacoes === 1
                ? "1 indicação encaminhada"
                : "$indicacoes indicações encaminhadas";
        }

        if ($respostas > 0) {
            $motivos[] = $respostas === 1
                ? "1 resposta publicada"
                : "$respostas respostas publicadas";
        }

        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não é possível remover {$vereador['nome']}: há "
                . implode(" e ", $motivos)
                . ". Remover apagaria o histórico que os cidadãos acompanham."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM vereador WHERE id_vereador = ?");
    $stmt->bind_param("i", $idVereador);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "{$vereador['nome']} foi removido."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao remover o vereador."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
