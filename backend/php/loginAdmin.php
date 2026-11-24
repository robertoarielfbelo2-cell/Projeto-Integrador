<?php

header('Content-Type: application/json; charset=UTF-8');

// 1. Permite apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}


session_start();


$dados = json_decode(
    file_get_contents('php://input'),
    true
);


if (!is_array($dados)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ]);
    exit;
}

$email = trim($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

// 4. Valida os campos
if ($email === '' || $senha === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha o email e a senha.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Email inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {


    $sql = "
        SELECT
            id_administrador,
            nome,
            email,
            senha,
            nivel_de_acesso
        FROM administrador
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('s', $email);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $administrador = $resultado->fetch_assoc();

    $stmt->close();

        if (
            !$administrador ||
            $senha !== $administrador['senha']
        ) {
        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Email ou senha incorretos.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    session_regenerate_id(true);


    unset(
        $_SESSION['id_cidadao'],
        $_SESSION['cidadao_nome'],
        $_SESSION['cidadao_email'],
        $_SESSION['id_vereador'],
        $_SESSION['vereador_nome'],
        $_SESSION['vereador_email']
    );

  
    $_SESSION['id_administrador'] =
        (int) $administrador['id_administrador'];

    $_SESSION['administrador_nome'] =
        $administrador['nome'];

    $_SESSION['administrador_email'] =
        $administrador['email'];

    $_SESSION['nivel_de_acesso'] =
        $administrador['nivel_de_acesso'];

    // 9. Retorna sucesso
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login administrativo realizado com sucesso!',
        'nome' => $administrador['nome'],
        'nivel_de_acesso' => $administrador['nivel_de_acesso']
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log(
        'Erro no login administrativo: ' .
        $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao realizar login.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}