<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Apenas administradores podem cadastrar partidos
if (
    !isset($_SESSION['id_administrador']) ||
    !is_numeric($_SESSION['id_administrador'])
) {
    http_response_code(403);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Acesso permitido apenas para administradores."
    ]);

    exit;
}

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

require_once 'conexao.php';

try {
    // Recebe o JSON enviado pelo JavaScript
    $dados = json_decode(
        file_get_contents("php://input"),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($dados)) {
        throw new InvalidArgumentException(
            "Os dados enviados são inválidos."
        );
    }

    // Obtém os campos
    $nome = trim((string)($dados["nome"] ?? ''));
    $sigla = strtoupper(trim((string)($dados["sigla"] ?? '')));
    $numero = $dados["numero"] ?? null;

    // Verifica os campos obrigatórios
    if ($nome === '' || $sigla === '' || $numero === null || $numero === '') {
        throw new InvalidArgumentException(
            "Preencha todos os campos obrigatórios."
        );
    }

    // Valida o tamanho do nome e da sigla
    if (
        mb_strlen($nome) > 100 ||
        mb_strlen($sigla) > 20
    ) {
        throw new InvalidArgumentException(
            "O nome ou a sigla ultrapassou o limite permitido."
        );
    }

    // Valida o número do partido
    $numeroValidado = filter_var(
        $numero,
        FILTER_VALIDATE_INT,
        ["options" => [
            "min_range" => 10,
            "max_range" => 99
        ]]
    );

    if ($numeroValidado === false) {
        throw new InvalidArgumentException(
            "O número do partido deve estar entre 10 e 99."
        );
    }

    // Verifica se o nome, a sigla ou o número já estão cadastrados
    $sqlVerificacao = "
        SELECT id_partido
        FROM partido
        WHERE nome = ? OR sigla = ? OR numero = ?
        LIMIT 1
    ";

    $stmtVerificacao = $conn->prepare($sqlVerificacao);

    $stmtVerificacao->bind_param(
        "ssi",
        $nome,
        $sigla,
        $numeroValidado
    );

    $stmtVerificacao->execute();

    $resultado = $stmtVerificacao->get_result();
    $partidoExiste = $resultado->num_rows > 0;

    $stmtVerificacao->close();

    if ($partidoExiste) {
        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Já existe um partido com esse nome, sigla ou número."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // Insere o partido no banco
    $sql = "
        INSERT INTO partido (nome, sigla, numero)
        VALUES (?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssi",
        $nome,
        $sigla,
        $numeroValidado
    );

    $stmt->execute();
    $stmt->close();

    http_response_code(201);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Partido cadastrado com sucesso!"
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
        "mensagem" => "JSON inválido."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao cadastrar partido."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();