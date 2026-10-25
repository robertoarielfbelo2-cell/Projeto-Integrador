<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Apenas administradores podem editar partidos
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

// Aceita somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once 'conexao.php';

try {
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

    $idPartido = filter_var(
        $dados["id"] ?? '',
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );
    $nome = trim((string)($dados["nome"] ?? ''));
    $sigla = strtoupper(trim((string)($dados["sigla"] ?? '')));
    $numero = $dados["numero"] ?? null;

    if ($idPartido === false) {
        throw new InvalidArgumentException("Partido inválido.");
    }

    // Mesmas regras do cadastro
    if ($nome === '' || $sigla === '' || $numero === null || $numero === '') {
        throw new InvalidArgumentException(
            "Preencha todos os campos obrigatórios."
        );
    }

    if (
        mb_strlen($nome) > 100 ||
        mb_strlen($sigla) > 20
    ) {
        throw new InvalidArgumentException(
            "O nome ou a sigla ultrapassou o limite permitido."
        );
    }

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

    // O partido precisa existir
    $stmtExiste = $conn->prepare(
        "SELECT 1 FROM partido WHERE id_partido = ? LIMIT 1"
    );
    $stmtExiste->bind_param("i", $idPartido);
    $stmtExiste->execute();
    $stmtExiste->store_result();
    $partidoExiste = $stmtExiste->num_rows > 0;
    $stmtExiste->close();

    if (!$partidoExiste) {
        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Partido não encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // Nome, sigla e número não podem repetir os de OUTRO partido
    $stmtDuplicado = $conn->prepare("
        SELECT 1
        FROM partido
        WHERE (nome = ? OR sigla = ? OR numero = ?)
          AND id_partido <> ?
        LIMIT 1
    ");
    $stmtDuplicado->bind_param(
        "ssii",
        $nome,
        $sigla,
        $numeroValidado,
        $idPartido
    );
    $stmtDuplicado->execute();
    $stmtDuplicado->store_result();
    $duplicado = $stmtDuplicado->num_rows > 0;
    $stmtDuplicado->close();

    if ($duplicado) {
        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Já existe outro partido com esse nome, sigla ou número."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE partido
        SET nome = ?, sigla = ?, numero = ?
        WHERE id_partido = ?
    ");
    $stmt->bind_param(
        "ssii",
        $nome,
        $sigla,
        $numeroValidado,
        $idPartido
    );
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Partido atualizado com sucesso!"
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
        "mensagem" => "Erro interno ao atualizar o partido."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
