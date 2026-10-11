<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

session_start();

// Apaga os dados da sessão
$_SESSION = [];

// Remove o cookie da sessão, se existir
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

// Encerra a sessão no servidor
session_destroy();

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Sessão encerrada com sucesso!'
], JSON_UNESCAPED_UNICODE);