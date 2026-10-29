<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Lista os projetos de lei do vereador logado, do mais novo para o mais antigo

if (!isset($_SESSION['id_vereador'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Vereador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idVereador = (int) $_SESSION['id_vereador'];

require_once __DIR__ . '/conexao.php';

try {

    $stmt = $conn->prepare("
        SELECT
            p.id_projeto,
            p.titulo,
            p.ementa,
            p.texto,
            p.anexo_pdf,
            p.data_publicacao,
            s.id_situacao,
            s.nome_da_situacao AS situacao,
            (
                SELECT COUNT(*)
                FROM apoio_projeto AS a
                WHERE a.id_projeto = p.id_projeto
            ) AS total_apoios
        FROM projeto_lei AS p
        INNER JOIN situacao_projeto AS s
            ON p.id_situacao = s.id_situacao
        WHERE p.id_vereador = ?
        ORDER BY p.data_publicacao DESC, p.id_projeto DESC
    ");
    $stmt->bind_param('i', $idVereador);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $projetos = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_projeto'] = (int) $linha['id_projeto'];
        $linha['id_situacao'] = (int) $linha['id_situacao'];
        $linha['total_apoios'] = (int) $linha['total_apoios'];
        $projetos[] = $linha;
    }

    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'projetos' => $projetos
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao listar projetos do vereador: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar os projetos de lei.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
