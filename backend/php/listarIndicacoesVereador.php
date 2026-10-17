<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Lista as indicações encaminhadas ao vereador logado (RF13).
// Filtro opcional: ?status=<id_status>

// 1. Exige um vereador logado
if (!isset($_SESSION['id_vereador'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Vereador não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// O id vem SEMPRE da sessão, nunca do navegador: assim um vereador
// não consegue ver as indicações de outro trocando um parâmetro.
$idVereador = (int) $_SESSION['id_vereador'];

// 2. Filtro de status (0 = todos)
$idStatus = 0;
$statusTexto = trim($_GET['status'] ?? '');

if ($statusTexto !== '') {

    if (!ctype_digit($statusTexto)) {
        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Status inválido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idStatus = (int) $statusTexto;
}

require_once __DIR__ . '/conexao.php';

try {

    $sql = "
        SELECT
            i.id_indicacao,
            i.titulo,
            i.descricao,
            i.foto_ou_anexo,
            i.data_de_cadastro,

            c.nome AS autor,

            l.cep,
            l.rua,
            l.numero,
            l.bairro,
            l.municipio,
            l.estado,

            s.id_status,
            s.nome_do_status AS status,

            (
                SELECT COUNT(*)
                FROM apoio AS a
                WHERE a.id_indicacao = i.id_indicacao
            ) AS total_apoios

        FROM indicacao AS i

        INNER JOIN cidadao AS c
            ON i.id_cidadao = c.id_cidadao

        INNER JOIN localizacao AS l
            ON i.id_localizacao = l.id_localizacao

        INNER JOIN status_indicacao AS s
            ON i.id_status = s.id_status

        WHERE i.id_vereador = ?
          AND (? = 0 OR i.id_status = ?)

        ORDER BY i.data_de_cadastro DESC, i.id_indicacao DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $idVereador, $idStatus, $idStatus);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $indicacoes = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_indicacao'] = (int) $linha['id_indicacao'];
        $linha['id_status'] = (int) $linha['id_status'];
        $linha['total_apoios'] = (int) $linha['total_apoios'];

        $indicacoes[] = $linha;
    }

    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'indicacoes' => $indicacoes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao listar indicações do vereador: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar as indicações.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
