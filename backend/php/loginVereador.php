<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Login do vereador (RF12): e-mail e senha definidos pelo administrador.

// 1. Permite apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Recebe os dados
$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = strtolower(trim((string)($dados['email'] ?? '')));
$senha = (string)($dados['senha'] ?? '');

// 3. Valida os campos
if ($email === '' || $senha === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha o e-mail e a senha.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'E-mail inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // 4. Busca o vereador pelo e-mail
    $sql = "
        SELECT
            id_vereador,
            TRIM(nome_parlamentar) AS nome_parlamentar,
            email,
            senha
        FROM vereador
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $vereador = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    // 5. Confere a senha com o hash gravado (RNF02).
    // A mesma mensagem para "e-mail não existe" e "senha errada" evita
    // revelar quais e-mails estão cadastrados.
    if (
        !$vereador ||
        $vereador['senha'] === null ||
        !password_verify($senha, $vereador['senha'])
    ) {
        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'E-mail ou senha incorretos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 6. Renova o identificador da sessão
    session_regenerate_id(true);

    // Remove eventuais sessões de cidadão e de administrador
    unset(
        $_SESSION['id_cidadao'],
        $_SESSION['cidadao_nome'],
        $_SESSION['cidadao_email'],
        $_SESSION['id_administrador'],
        $_SESSION['administrador_nome'],
        $_SESSION['administrador_email'],
        $_SESSION['nivel_de_acesso']
    );

    // 7. Cria a sessão do vereador
    $_SESSION['id_vereador'] = (int) $vereador['id_vereador'];
    $_SESSION['vereador_nome'] = $vereador['nome_parlamentar'];
    $_SESSION['vereador_email'] = $vereador['email'];

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso!',
        'nome' => $vereador['nome_parlamentar']
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro no login do vereador: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao realizar login.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
