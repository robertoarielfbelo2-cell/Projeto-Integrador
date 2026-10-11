<?php

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);
    exit;
}

require_once "conexao.php";

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

    $nome = trim((string)($dados["nome"] ?? ""));
    $email = strtolower(trim((string)($dados["email"] ?? "")));
    $telefone = preg_replace('/\D/', '', (string)($dados["telefone"] ?? ""));
    $dataNascimento = (string)($dados["data_nascimento"] ?? "");
    $senha = (string)($dados["senha"] ?? "");
    $cpf = preg_replace('/\D/', '', (string)($dados["cpf"] ?? ""));

    // Verifica campos obrigatórios
    if (
        $nome === "" ||
        $email === "" ||
        $dataNascimento === "" ||
        $senha === "" ||
        $cpf === ""
    ) {
        throw new InvalidArgumentException(
            "Preencha todos os campos obrigatórios."
        );
    }

    // Valida o tamanho do nome
    if (mb_strlen($nome) > 100) {
        throw new InvalidArgumentException(
            "O nome deve ter no máximo 100 caracteres."
        );
    }

    // Valida e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException(
            "Informe um e-mail válido."
        );
    }

    // Verifica apenas se o CPF possui 11 números
    if (strlen($cpf) !== 11) {
        throw new InvalidArgumentException(
            "O CPF deve conter 11 números."
        );
    }

    // Valida telefone, caso informado
    if (
        $telefone !== "" &&
        !in_array(strlen($telefone), [10, 11], true)
    ) {
        throw new InvalidArgumentException(
            "Informe um telefone válido."
        );
    }

    // Verifica tamanho mínimo da senha
    if (strlen($senha) < 1) {
        throw new InvalidArgumentException(
            "A senha deve conter pelo menos 1 caracteres."
        );
    }

    // Converte a data de nascimento
    $nascimento = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $dataNascimento
    );

    if (
        !$nascimento ||
        $nascimento->format('Y-m-d') !== $dataNascimento
    ) {
        throw new InvalidArgumentException(
            "Informe uma data de nascimento válida."
        );
    }

    // Verifica se o cidadão tem 18 anos completos
    $hoje = new DateTimeImmutable("today");

    if ($nascimento > $hoje) {
        throw new InvalidArgumentException(
            "A data de nascimento não pode ser futura."
        );
    }

    $idade = $nascimento->diff($hoje)->y;

    if ($idade < 18) {
        throw new InvalidArgumentException(
            "O cadastro é permitido apenas para maiores de 18 anos."
        );
    }

    // Verifica se CPF ou e-mail já estão cadastrados
    $sqlVerificacao = "
        SELECT id_cidadao
        FROM cidadao
        WHERE email = ? OR cpf = ?
        LIMIT 1
    ";

    $stmtVerificacao = $conn->prepare($sqlVerificacao);
    $stmtVerificacao->bind_param("ss", $email, $cpf);
    $stmtVerificacao->execute();

    $resultado = $stmtVerificacao->get_result();
    $jaExiste = $resultado->num_rows > 0;

    $stmtVerificacao->close();

    if ($jaExiste) {
        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "E-mail ou CPF já cadastrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // Protege a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere cidadão
    $sql = "
        INSERT INTO cidadao (
            nome,
            email,
            telefone,
            data_nascimento,
            senha,
            cpf
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssss",
        $nome,
        $email,
        $telefone,
        $dataNascimento,
        $senhaHash,
        $cpf
    );

    $stmt->execute();
    $stmt->close();

    http_response_code(201);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Cidadão cadastrado com sucesso!"
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
        "mensagem" => "Os dados enviados são inválidos."
    ], JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro no banco de dados ao cadastrar cidadão."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao cadastrar cidadão."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();