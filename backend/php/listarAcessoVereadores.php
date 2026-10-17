<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Lista os vereadores com o e-mail de acesso. Só o administrador vê os e-mails.
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

require_once __DIR__ . "/conexao.php";

try {

    $sql = "
        SELECT
            v.id_vereador,
            TRIM(v.nome_parlamentar) AS nome_parlamentar,
            p.sigla,
            v.email
        FROM vereador AS v
        INNER JOIN partido AS p
            ON v.id_partido = p.id_partido
        ORDER BY TRIM(v.nome_parlamentar) ASC
    ";

    $resultado = $conn->query($sql);

    $vereadores = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_vereador'] = (int) $linha['id_vereador'];
        $vereadores[] = $linha;
    }

    echo json_encode([
        "sucesso" => true,
        "vereadores" => $vereadores
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log("Erro ao listar acesso dos vereadores: " . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao carregar os vereadores."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
