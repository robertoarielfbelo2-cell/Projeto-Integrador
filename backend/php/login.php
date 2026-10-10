<?php
header('Content-Type: application/json; charset=UTF-8');

$dados = json_decode(file_get_contents('php://input'), true);
$email = $dados['email'] ?? '';
$senhaLogin = $dados['senha'] ?? '';

if (!is_string($email) || !is_string($senhaLogin) || trim($email) === '' || $senhaLogin === '') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Informe seu e-mail e sua senha.']);
    exit;
}

$email = trim($email);

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/conexao.php';
    $conn->set_charset('utf8mb4');

    $sql = 'SELECT id_cidadao, nome, email, senha FROM cidadao WHERE email = ? LIMIT 2';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($idCidadao, $nome, $emailCadastrado, $senhaHash);

    $sucesso = false;
    $mensagem = 'E-mail ou senha incorretos.';

    if ($stmt->num_rows === 1) {
        $stmt->fetch();

        if (password_verify($senhaLogin, $senhaHash)) {
            session_start();
            session_regenerate_id(true);
            $_SESSION['id_cidadao'] = $idCidadao;
            $_SESSION['cidadao_nome'] = $nome;
            $_SESSION['cidadao_email'] = $emailCadastrado;

            $sucesso = true;
            $mensagem = 'Login realizado com sucesso!';
        }
    }

    $stmt->close();
    $conn->close();
    echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
} catch (Throwable $erro) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao realizar o login.']);
}
