<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Lista pública dos projetos de lei, do mais novo para o mais antigo.
// Filtro opcional: ?situacao=<id_situacao>
// Para o cidadão logado, informa também se ele já apoiou cada projeto.

$idCidadao = isset($_SESSION['id_cidadao']) ? (int) $_SESSION['id_cidadao'] : 0;

$idSituacao = 0;
$situacaoTexto = trim($_GET['situacao'] ?? '');

if ($situacaoTexto !== '') {

    if (!ctype_digit($situacaoTexto)) {
        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Situação inválida.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idSituacao = (int) $situacaoTexto;
}

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
            TRIM(v.nome_parlamentar) AS vereador,
            pa.sigla AS vereador_partido,
            (
                SELECT COUNT(*)
                FROM apoio_projeto AS a
                WHERE a.id_projeto = p.id_projeto
            ) AS total_apoios,
            (
                SELECT COUNT(*)
                FROM apoio_projeto AS a2
                WHERE a2.id_projeto = p.id_projeto
                  AND a2.id_cidadao = ?
            ) AS ja_apoiou
        FROM projeto_lei AS p
        INNER JOIN situacao_projeto AS s
            ON p.id_situacao = s.id_situacao
        INNER JOIN vereador AS v
            ON p.id_vereador = v.id_vereador
        INNER JOIN partido AS pa
            ON v.id_partido = pa.id_partido
        WHERE (? = 0 OR p.id_situacao = ?)
        ORDER BY p.data_publicacao DESC, p.id_projeto DESC
    ");
    $stmt->bind_param('iii', $idCidadao, $idSituacao, $idSituacao);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $projetos = [];

    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_projeto'] = (int) $linha['id_projeto'];
        $linha['id_situacao'] = (int) $linha['id_situacao'];
        $linha['total_apoios'] = (int) $linha['total_apoios'];
        $linha['ja_apoiou'] = (int) $linha['ja_apoiou'] > 0;
        $projetos[] = $linha;
    }

    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'projetos' => $projetos
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log('Erro ao listar projetos de lei: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar os projetos de lei.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
