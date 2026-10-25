<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Informa quem está logado neste navegador, para o cabeçalho de todas as páginas.
// Os logins limpam a sessão dos outros perfis, então só um deles existe por vez.
if (isset($_SESSION['id_administrador'])) {

    echo json_encode([
        'perfil' => 'administrador',
        'nome' => $_SESSION['administrador_nome'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

} elseif (isset($_SESSION['id_vereador'])) {

    echo json_encode([
        'perfil' => 'vereador',
        'nome' => $_SESSION['vereador_nome'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

} elseif (isset($_SESSION['id_cidadao'])) {

    echo json_encode([
        'perfil' => 'cidadao',
        'nome' => $_SESSION['cidadao_nome'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        'perfil' => null,
        'nome' => ''
    ], JSON_UNESCAPED_UNICODE);
}
