<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);

    exit;
}

// Encerra a sessão atual
$_SESSION = [];

if (ini_get('session.use_cookies')) {

    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Sessão encerrada com sucesso.'
], JSON_UNESCAPED_UNICODE);