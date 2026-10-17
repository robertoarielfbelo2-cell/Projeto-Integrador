<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Informa se existe um vereador logado (usado pelo painel do vereador)
if (isset($_SESSION['id_vereador'])) {

    echo json_encode([
        'logado' => true,
        'id_vereador' => (int) $_SESSION['id_vereador'],
        'nome' => $_SESSION['vereador_nome']
    ], JSON_UNESCAPED_UNICODE);

} else {

    http_response_code(401);

    echo json_encode([
        'logado' => false,
        'mensagem' => 'Vereador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
}
