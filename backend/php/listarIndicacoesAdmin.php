<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Somente administradores podem acessar
if (!isset($_SESSION['id_administrador'])) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Acesso não autorizado.'
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
            l.bairro,
            l.municipio,
            l.estado,

            s.id_status,
            s.nome_do_status AS status,

            TRIM(v.nome_parlamentar) AS vereador,
            p.sigla AS vereador_partido,

            (
                SELECT COUNT(*)
                FROM apoio a
                WHERE a.id_indicacao = i.id_indicacao
            ) AS total_apoios

        FROM indicacao i

        INNER JOIN cidadao c
            ON i.id_cidadao = c.id_cidadao

        INNER JOIN localizacao l
            ON i.id_localizacao = l.id_localizacao

        INNER JOIN status_indicacao s
            ON i.id_status = s.id_status

        -- LEFT JOIN: a indicação aparece mesmo sem vereador (id_vereador NULL)
        LEFT JOIN vereador v
            ON i.id_vereador = v.id_vereador

        LEFT JOIN partido p
            ON v.id_partido = p.id_partido

        ORDER BY
            i.data_de_cadastro DESC,
            i.id_indicacao DESC
    ";

    $resultado = $conn->query($sql);

    $indicacoes = [];

    while ($linha = $resultado->fetch_assoc()) {

        $linha['id_indicacao'] =
            (int) $linha['id_indicacao'];

        $linha['id_status'] =
            (int) $linha['id_status'];

        $linha['total_apoios'] =
            (int) $linha['total_apoios'];

        $indicacoes[] = $linha;
    }

    echo json_encode([
        'sucesso' => true,
        'indicacoes' => $indicacoes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    error_log(
        'Erro ao listar indicações administrativas: ' .
        $erro->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar indicações.'
    ], JSON_UNESCAPED_UNICODE);

} finally {

    $conn->close();

}