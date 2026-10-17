<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// O vereador registra uma resposta pública em uma indicação encaminhada a ele (RF14).

// 1. Exige um vereador logado
if (!isset($_SESSION['id_vereador'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Vereador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idVereador = (int) $_SESSION['id_vereador'];

// 2. Recebe e valida os dados
$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idIndicacao = filter_var(
    $dados['id_indicacao'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$texto = trim((string)($dados['texto'] ?? ''));

if ($idIndicacao === false) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Indicação inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($texto === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Escreva a resposta antes de publicar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($texto) > 1000) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'A resposta deve ter no máximo 1000 caracteres.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

try {

    // 3. Regra de segurança do módulo: a indicação precisa existir
    // E estar encaminhada ao vereador da sessão.
    $stmtDono = $conn->prepare(
        'SELECT id_vereador FROM indicacao WHERE id_indicacao = ? LIMIT 1'
    );
    $stmtDono->bind_param('i', $idIndicacao);
    $stmtDono->execute();

    $indicacao = $stmtDono->get_result()->fetch_assoc();

    $stmtDono->close();

    if (!$indicacao) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Indicação não encontrada.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ((int) $indicacao['id_vereador'] !== $idVereador) {
        http_response_code(403);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Esta indicação não foi encaminhada a você.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Grava a resposta
    $stmt = $conn->prepare(
        'INSERT INTO resposta_indicacao (texto, id_indicacao, id_vereador) VALUES (?, ?, ?)'
    );
    $stmt->bind_param('sii', $texto, $idIndicacao, $idVereador);
    $stmt->execute();

    $idResposta = $conn->insert_id;

    $stmt->close();

    // 5. Devolve a resposta criada, para a tela exibi-la sem recarregar
    $stmtNova = $conn->prepare(
        'SELECT id_resposta, texto, data_resposta FROM resposta_indicacao WHERE id_resposta = ?'
    );
    $stmtNova->bind_param('i', $idResposta);
    $stmtNova->execute();

    $novaResposta = $stmtNova->get_result()->fetch_assoc();

    $stmtNova->close();

    $novaResposta['id_resposta'] = (int) $novaResposta['id_resposta'];
    $novaResposta['vereador'] = $_SESSION['vereador_nome'];

    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Resposta publicada com sucesso!',
        'resposta' => $novaResposta
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao responder indicação: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível publicar a resposta.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
