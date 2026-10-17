<?php

header('Content-Type: application/json; charset=UTF-8');

// Permite apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

// Recebe os dados enviados pelo JavaScript
$dados = json_decode(file_get_contents('php://input'), true);

$email = $dados['email'] ?? '';
$senhaLogin = $dados['senha'] ?? '';

// Verifica se os campos foram preenchidos
if (
    !is_string($email) ||
    !is_string($senhaLogin) ||
    trim($email) === '' ||
    $senhaLogin === ''
) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Informe seu e-mail e sua senha.'
    ]);
    exit;
}

$email = trim($email);

try {
    require_once __DIR__ . '/conexao.php';

    // Procura o cidadão pelo e-mail
    $sql = '
        SELECT
            id_cidadao,
            nome,
            email,
            senha,
            data_nascimento
        FROM cidadao
        WHERE email = ?
        LIMIT 1
    ';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $stmt->bind_result(
        $idCidadao,
        $nome,
        $emailCadastrado,
        $senhaHash,
        $dataNascimento
    );

    // Verifica se encontrou o cidadão
    if (!$stmt->fetch()) {
        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'E-mail ou senha incorretos.'
        ]);

        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Verifica a senha cadastrada
    if (!password_verify($senhaLogin, $senhaHash)) {
        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'E-mail ou senha incorretos.'
        ]);

        $conn->close();
        exit;
    }

    // Verifica a idade do cidadão
    $nascimento = new DateTimeImmutable($dataNascimento);
    $hoje = new DateTimeImmutable('today');

    $idade = $nascimento->diff($hoje)->y;

    if ($nascimento > $hoje || $idade < 18) {
        http_response_code(403);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'O acesso é permitido apenas para maiores de 18 anos.'
        ]);

        $conn->close();
        exit;
    }

    // Cria a sessão do cidadão
    session_start();
    session_regenerate_id(true);

    // Remove uma possível sessão de administrador ou de vereador
    unset(
        $_SESSION['id_administrador'],
        $_SESSION['administrador_nome'],
        $_SESSION['administrador_email'],
        $_SESSION['nivel_de_acesso'],
        $_SESSION['id_vereador'],
        $_SESSION['vereador_nome'],
        $_SESSION['vereador_email']
    );

    // Cria a sessão do cidadão
    $_SESSION['id_cidadao'] = $idCidadao;
    $_SESSION['cidadao_nome'] = $nome;
    $_SESSION['cidadao_email'] = $emailCadastrado;

    // Retorna sucesso ao JavaScript
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso!'
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();

} catch (Throwable $erro) {
    error_log('Erro no login: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao realizar o login.'
    ], JSON_UNESCAPED_UNICODE);
}