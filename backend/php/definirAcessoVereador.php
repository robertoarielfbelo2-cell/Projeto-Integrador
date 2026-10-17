<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Define (ou troca) o e-mail e a senha de acesso de um vereador (RF09 / RF12).
// Somente o administrador pode fazer isso.
if (
    !isset($_SESSION['id_administrador']) ||
    !is_numeric($_SESSION['id_administrador'])
) {
    http_response_code(403);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Acesso permitido apenas para administradores."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once __DIR__ . "/conexao.php";

try {

    $dados = json_decode(
        file_get_contents("php://input"),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($dados)) {
        throw new InvalidArgumentException("Dados inválidos.");
    }

    $idVereador = filter_var(
        $dados["id_vereador"] ?? '',
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );
    $email = strtolower(trim((string)($dados["email"] ?? '')));
    $senha = (string)($dados["senha"] ?? '');

    if ($idVereador === false) {
        throw new InvalidArgumentException("Selecione um vereador.");
    }

    if (
        $email === '' ||
        mb_strlen($email) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        throw new InvalidArgumentException("Informe um e-mail válido.");
    }

    if (strlen($senha) < 8) {
        throw new InvalidArgumentException(
            "A senha deve ter pelo menos 8 caracteres."
        );
    }

    // O vereador precisa existir
    $stmtVereador = $conn->prepare(
        "SELECT 1 FROM vereador WHERE id_vereador = ? LIMIT 1"
    );
    $stmtVereador->bind_param("i", $idVereador);
    $stmtVereador->execute();
    $stmtVereador->store_result();
    $vereadorExiste = $stmtVereador->num_rows > 0;
    $stmtVereador->close();

    if (!$vereadorExiste) {
        throw new InvalidArgumentException("O vereador selecionado não existe.");
    }

    // O e-mail não pode estar em uso por OUTRO vereador
    $stmtEmail = $conn->prepare(
        "SELECT 1 FROM vereador WHERE email = ? AND id_vereador <> ? LIMIT 1"
    );
    $stmtEmail->bind_param("si", $email, $idVereador);
    $stmtEmail->execute();
    $stmtEmail->store_result();
    $emailEmUso = $stmtEmail->num_rows > 0;
    $stmtEmail->close();

    if ($emailEmUso) {
        throw new InvalidArgumentException(
            "Este e-mail já é usado por outro vereador."
        );
    }

    // A senha nunca é gravada em texto puro (RNF02)
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE vereador SET email = ?, senha = ? WHERE id_vereador = ?"
    );
    $stmt->bind_param("ssi", $email, $senhaHash, $idVereador);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Acesso do vereador salvo com sucesso!"
    ], JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $erro) {

    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => $erro->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} catch (JsonException $erro) {

    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Os dados enviados não são um JSON válido."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log("Erro ao definir acesso do vereador: " . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao salvar o acesso do vereador."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
