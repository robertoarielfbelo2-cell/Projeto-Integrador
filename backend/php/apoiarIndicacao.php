<?php

header('Content-Type: application/json; charset=UTF-8');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

session_start();

if (!isset($_SESSION['id_cidadao'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para apoiar uma indicação.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idCidadao = (int) $_SESSION['id_cidadao'];

$dados = json_decode(file_get_contents('php://input'), true);

$idIndicacao = filter_var(
    $dados['id_indicacao'] ?? null,
    FILTER_VALIDATE_INT
);

if ($idIndicacao === false || $idIndicacao === null || $idIndicacao <= 0) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Indicação inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    require_once __DIR__ . '/conexao.php';

    $sqlIndicacao = "
        SELECT id_indicacao
        FROM indicacao
        WHERE id_indicacao = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sqlIndicacao);
    $stmt->bind_param('i', $idIndicacao);
    $stmt->execute();
    $stmt->store_result();

    $indicacaoExiste = $stmt->num_rows > 0;

    $stmt->close();

    if (!$indicacaoExiste) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Indicação não encontrada.'
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }


    $sqlVerificarApoio = "
        SELECT id_cidadao
        FROM apoio
        WHERE id_cidadao = ?
          AND id_indicacao = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sqlVerificarApoio);

    $stmt->bind_param(
        'ii',
        $idCidadao,
        $idIndicacao
    );

    $stmt->execute();
    $stmt->store_result();

    $jaApoiou = $stmt->num_rows > 0;

    $stmt->close();

    if ($jaApoiou) {
        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Você já apoiou esta indicação.'
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }


    $sqlApoio = "
        INSERT INTO apoio (
            id_cidadao,
            id_indicacao
        )
        VALUES (?, ?)
    ";

    $stmt = $conn->prepare($sqlApoio);

    $stmt->bind_param(
        'ii',
        $idCidadao,
        $idIndicacao
    );

    $stmt->execute();
    $stmt->close();


    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM apoio
        WHERE id_indicacao = ?
    ";

    $stmt = $conn->prepare($sqlTotal);
    $stmt->bind_param('i', $idIndicacao);
    $stmt->execute();

    $stmt->bind_result($totalApoios);
    $stmt->fetch();

    $stmt->close();


    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Indicação apoiada com sucesso!',
        'total_apoios' => (int) $totalApoios
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();

} catch (mysqli_sql_exception $erro) {

    error_log('Erro ao apoiar indicação: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao registrar o apoio.'
    ], JSON_UNESCAPED_UNICODE);
}