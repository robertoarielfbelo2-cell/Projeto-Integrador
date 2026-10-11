<?php

header("Content-Type: application/json; charset=UTF-8");

// Permite apenas consultas GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {

    require_once __DIR__ . "/conexao.php";

    // Busca os vereadores e seus partidos
    $sql = "
        SELECT
            v.id_vereador,
            TRIM(v.nome_parlamentar) AS nome_parlamentar,
            v.descricao,
            v.foto,
            p.sigla
        FROM vereador AS v
        INNER JOIN partido AS p
            ON v.id_partido = p.id_partido
        ORDER BY TRIM(v.nome_parlamentar) ASC
    ";

    $resultado = $conn->query($sql);

    // Organiza os vereadores em um array
    $vereadores = [];

    while ($linha = $resultado->fetch_assoc()) {
        $vereadores[] = $linha;
    }

    // Retorna os dados para o JavaScript
    echo json_encode(
        $vereadores,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

} catch (Throwable $erro) {

    error_log(
        "Erro ao listar vereadores: " . $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "erro" => "Não foi possível carregar os vereadores."
    ], JSON_UNESCAPED_UNICODE);

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}