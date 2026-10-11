<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

if (isset($_SESSION['id_cidadao'])) {

    echo json_encode([
        'logado' => true,
        'nome' => $_SESSION['cidadao_nome'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        'logado' => false
    ], JSON_UNESCAPED_UNICODE);

}