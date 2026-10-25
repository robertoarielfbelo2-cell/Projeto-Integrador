<?php

header("Content-Type: application/json; charset=UTF-8");

session_start();

// Edita o registro de um vereador (RF09). Somente administradores.
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

require_once 'conexao.php';

try {
    $dados = json_decode(
        file_get_contents("php://input"),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($dados)) {
        throw new InvalidArgumentException("JSON inválido.");
    }

    // Mesmos campos do cadastro, mais o id do vereador
    $idVereador = filter_var(
        $dados["id"] ?? '',
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );
    $nomeVereador = trim((string)($dados["nomeV"] ?? ''));
    $inicioMandato = (string)($dados["inicioMandato"] ?? '');
    $fimMandato = (string)($dados["fimMandato"] ?? '');
    $descricao = trim((string)($dados["descricao"] ?? ''));
    $foto = trim((string)($dados["foto"] ?? ''));
    $partidoVereador = $dados["partidoVereador"] ?? '';

    if ($idVereador === false) {
        throw new InvalidArgumentException("Vereador inválido.");
    }

    if (
        $nomeVereador === '' ||
        $inicioMandato === '' ||
        $fimMandato === '' ||
        $partidoVereador === ''
    ) {
        throw new InvalidArgumentException(
            "Preencha todos os campos obrigatórios."
        );
    }

    if (mb_strlen($nomeVereador) > 100) {
        throw new InvalidArgumentException(
            "O nome parlamentar deve ter no máximo 100 caracteres."
        );
    }

    $inicio = DateTimeImmutable::createFromFormat('!Y-m-d', $inicioMandato);
    $fim = DateTimeImmutable::createFromFormat('!Y-m-d', $fimMandato);

    if (
        !$inicio ||
        !$fim ||
        $inicio->format('Y-m-d') !== $inicioMandato ||
        $fim->format('Y-m-d') !== $fimMandato
    ) {
        throw new InvalidArgumentException("Informe datas válidas.");
    }

    if ($fim < $inicio) {
        throw new InvalidArgumentException(
            "O término do mandato não pode ser anterior ao início."
        );
    }

    $idPartido = filter_var(
        $partidoVereador,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($idPartido === false) {
        throw new InvalidArgumentException("Selecione um partido válido.");
    }

    if ($foto !== '' && !filter_var($foto, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException(
            "Informe uma URL válida para a fotografia."
        );
    }

    if ($foto !== '' && !in_array(
        strtolower(parse_url($foto, PHP_URL_SCHEME) ?? ''),
        ['http', 'https'],
        true
    )) {
        throw new InvalidArgumentException(
            "A fotografia deve utilizar HTTP ou HTTPS."
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
        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Vereador não encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    // O partido escolhido precisa existir
    $stmtPartido = $conn->prepare(
        "SELECT 1 FROM partido WHERE id_partido = ? LIMIT 1"
    );
    $stmtPartido->bind_param("i", $idPartido);
    $stmtPartido->execute();
    $stmtPartido->store_result();
    $partidoExiste = $stmtPartido->num_rows > 0;
    $stmtPartido->close();

    if (!$partidoExiste) {
        throw new InvalidArgumentException("O partido selecionado não existe.");
    }

    // E-mail e senha de acesso não mudam aqui: são da tela Acesso dos Vereadores
    $stmt = $conn->prepare("
        UPDATE vereador
        SET
            nome_parlamentar = ?,
            data_inicio_mandato = ?,
            data_termino_mandato = ?,
            descricao = ?,
            foto = ?,
            id_partido = ?
        WHERE id_vereador = ?
    ");
    $stmt->bind_param(
        "sssssii",
        $nomeVereador,
        $inicioMandato,
        $fimMandato,
        $descricao,
        $foto,
        $idPartido,
        $idVereador
    );
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Vereador atualizado com sucesso!"
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
    error_log($erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno ao atualizar o vereador."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
