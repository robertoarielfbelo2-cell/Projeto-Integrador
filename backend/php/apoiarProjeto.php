<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// O cidadão logado apoia um projeto de lei. Cada cidadão apoia um projeto uma única vez.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['id_cidadao'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado como cidadão para apoiar um projeto de lei.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idCidadao = (int) $_SESSION['id_cidadao'];

$dados = json_decode(file_get_contents('php://input'), true);

$idProjeto = filter_var(
    is_array($dados) ? ($dados['id_projeto'] ?? '') : '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idProjeto === false) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Projeto de lei inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // O projeto precisa existir
    $stmt = $conn->prepare('SELECT 1 FROM projeto_lei WHERE id_projeto = ? LIMIT 1');
    $stmt->bind_param('i', $idProjeto);
    $stmt->execute();
    $stmt->store_result();
    $projetoExiste = $stmt->num_rows > 0;
    $stmt->close();

    if (!$projetoExiste) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Projeto de lei não encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Um apoio por cidadão: a chave primária (id_cidadao, id_projeto) também garante isso no banco
    $stmt = $conn->prepare(
        'SELECT 1 FROM apoio_projeto WHERE id_cidadao = ? AND id_projeto = ? LIMIT 1'
    );
    $stmt->bind_param('ii', $idCidadao, $idProjeto);
    $stmt->execute();
    $stmt->store_result();
    $jaApoiou = $stmt->num_rows > 0;
    $stmt->close();

    if ($jaApoiou) {
        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Você já apoiou este projeto de lei.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO apoio_projeto (id_cidadao, id_projeto) VALUES (?, ?)');
    $stmt->bind_param('ii', $idCidadao, $idProjeto);
    $stmt->execute();
    $stmt->close();

    // Total atualizado, para a tela mostrar sem recarregar
    $stmt = $conn->prepare('SELECT COUNT(*) FROM apoio_projeto WHERE id_projeto = ?');
    $stmt->bind_param('i', $idProjeto);
    $stmt->execute();
    $stmt->bind_result($totalApoios);
    $stmt->fetch();
    $stmt->close();

    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Projeto de lei apoiado com sucesso!',
        'total_apoios' => (int) $totalApoios
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao apoiar projeto de lei: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível registrar o apoio.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
