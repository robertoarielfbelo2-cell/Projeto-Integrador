<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Devolve os dados de um vereador para preencher o formulário de edição: ?id=<id_vereador>
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

$idVereador = filter_var(
    $_GET['id'] ?? '',
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

    $stmt = $conn->prepare("
        SELECT
            id_vereador,
            TRIM(nome_parlamentar) AS nome_parlamentar,
            data_inicio_mandato,
            data_termino_mandato,
            descricao,
            foto,
            id_partido
        FROM vereador
        WHERE id_vereador = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $idVereador);
    $stmt->execute();

    $vereador = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$vereador) {
        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Vereador não encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    $vereador['id_vereador'] = (int) $vereador['id_vereador'];
    $vereador['id_partido'] = (int) $vereador['id_partido'];

    echo json_encode([
        "sucesso" => true,
        "vereador" => $vereador
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao buscar o vereador."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
