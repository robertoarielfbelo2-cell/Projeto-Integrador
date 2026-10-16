<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'erro' => 'Método não permitido.'
    ]);
    exit;
}

require_once __DIR__ . '/conexao.php';

// Identifica o cidadão logado.
// Se for visitante, utiliza 0.
$idCidadao = (int) ($_SESSION['id_cidadao'] ?? 0);

// Recebe os filtros enviados pelo JavaScript
$status = trim($_GET['status'] ?? '');
$cep = preg_replace('/\D/', '', $_GET['cep'] ?? '');
$bairro = trim($_GET['bairro'] ?? '');
$municipio = trim($_GET['municipio'] ?? '');

try {

    $sql = "
        SELECT
            i.id_indicacao,
            i.titulo,
            i.descricao,
            i.foto_ou_anexo,
            i.data_de_cadastro,
            s.nome_do_status AS status,
            l.cep,
            l.bairro,
            l.municipio,
            c.nome AS autor,
            TRIM(v.nome_parlamentar) AS vereador,
            p.sigla AS vereador_partido,

            (
                SELECT COUNT(*)
                FROM apoio AS a
                WHERE a.id_indicacao = i.id_indicacao
            ) AS total_apoios,

            (
                SELECT COUNT(*)
                FROM apoio AS a2
                WHERE a2.id_indicacao = i.id_indicacao
                  AND a2.id_cidadao = ?
            ) AS ja_apoiou

        FROM indicacao AS i

        INNER JOIN cidadao AS c
            ON i.id_cidadao = c.id_cidadao

        INNER JOIN localizacao AS l
            ON i.id_localizacao = l.id_localizacao

        INNER JOIN status_indicacao AS s
            ON i.id_status = s.id_status

        -- LEFT JOIN: a indicação aparece mesmo sem vereador (id_vereador NULL)
        LEFT JOIN vereador AS v
            ON i.id_vereador = v.id_vereador

        LEFT JOIN partido AS p
            ON v.id_partido = p.id_partido

        WHERE
            (? = '' OR s.nome_do_status = ?)

            AND (? = '' OR REPLACE(l.cep, '-', '') = ?)

            AND (? = '' OR l.bairro LIKE CONCAT('%', ?, '%'))

            AND (? = '' OR l.municipio LIKE CONCAT('%', ?, '%'))

        ORDER BY i.data_de_cadastro DESC, i.id_indicacao DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'issssssss',
        $idCidadao,
        $status,
        $status,
        $cep,
        $cep,
        $bairro,
        $bairro,
        $municipio,
        $municipio
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    $indicacoes = [];

    while ($linha = $resultado->fetch_assoc()) {

        // Converte o resultado para verdadeiro ou falso
        $linha['ja_apoiou'] = (bool) $linha['ja_apoiou'];

        // Converte a quantidade de apoios para número
        $linha['total_apoios'] = (int) $linha['total_apoios'];

        $indicacoes[] = $linha;
    }

    echo json_encode(
        $indicacoes,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    $stmt->close();

} catch (Throwable $erro) {

    error_log('Erro ao listar indicações: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível carregar as indicações.'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();