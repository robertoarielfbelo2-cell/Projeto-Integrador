<?php

header("Content-Type: application/json; charset=UTF-8");

require_once 'conexao.php';

try {
    $sql = "
        SELECT
            id_partido AS id,
            nome,
            sigla,
            numero
        FROM partido
        ORDER BY nome ASC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Falha ao consultar partidos.");
    }

    $partidos = [];

    while ($row = $result->fetch_assoc()) {
        $partidos[] = $row;
    }

    echo json_encode(
        $partidos,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "erro" => "Não foi possível consultar os partidos."
    ]);
}

$conn->close();