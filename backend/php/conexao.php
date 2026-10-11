<?php

$servidor = "localhost";
$usuario = "root";
$senhaBanco = "";
$banco = "projetointegrador";
$porta = 3306;

try {
    $conn = new mysqli(
        $servidor,
        $usuario,
        $senhaBanco,
        $banco,
        $porta
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $erro) {
    error_log("Erro na conexão com o banco: " . $erro->getMessage());

    http_response_code(500);

    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode([
        "erro" => "Não foi possível conectar ao banco de dados."
    ]);

    exit;
}