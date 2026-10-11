<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Verifica se existe um administrador logado
if (isset($_SESSION['id_administrador'])) {

    echo json_encode([
        'logado' => true,
        'nome' => $_SESSION['administrador_nome'],
        'nivel_de_acesso' => $_SESSION['nivel_de_acesso']
    ], JSON_UNESCAPED_UNICODE);

} else {

    http_response_code(401);

    echo json_encode([
        'logado' => false,
        'mensagem' => 'Administrador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

}