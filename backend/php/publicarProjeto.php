<?php

header('Content-Type: application/json; charset=UTF-8');

session_start();

// O vereador logado publica um projeto de lei.
// Campos: titulo, ementa, texto e anexo_pdf (opcional). A situação inicial é "Em tramitação" (id 1).

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

// O autor vem da sessão, nunca do navegador
$idVereador = (int) $_SESSION['id_vereador'];

$titulo = trim($_POST['titulo'] ?? '');
$ementa = trim($_POST['ementa'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Responde com erro 400 e encerra
function recusar($mensagem)
{
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => $mensagem
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($titulo === '' || $ementa === '' || $texto === '') {
    recusar('Preencha o título, a ementa e o texto do projeto.');
}

if (mb_strlen($titulo) > 150) {
    recusar('O título deve ter no máximo 150 caracteres.');
}

if (mb_strlen($ementa) > 500) {
    recusar('A ementa deve ter no máximo 500 caracteres.');
}

if (mb_strlen($texto) > 20000) {
    recusar('O texto deve ter no máximo 20000 caracteres.');
}

// PDF opcional: até 10 MB e precisa ser PDF de verdade (não só ter a extensão)
$arquivo = $_FILES['anexo_pdf'] ?? null;
$temArquivo = $arquivo !== null && $arquivo['error'] !== UPLOAD_ERR_NO_FILE;

if ($temArquivo) {

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        recusar('Erro ao receber o PDF. Tente enviar de novo.');
    }

    if ($arquivo['size'] <= 0 || $arquivo['size'] > 10 * 1024 * 1024) {
        recusar('O PDF deve ter no máximo 10 MB.');
    }

    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);

    if ($tipo !== 'application/pdf') {
        recusar('O anexo precisa ser um arquivo PDF.');
    }
}

require_once __DIR__ . '/conexao.php';

$caminhoFisico = null;

try {

    $caminhoAnexo = null;

    if ($temArquivo) {

        $pasta = __DIR__ . '/../../uploads/projetos/';

        if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            throw new RuntimeException('Não foi possível criar a pasta de anexos.');
        }

        // Nome aleatório: evita sobrescrever arquivos e nomes maliciosos
        $nomeArquivo = bin2hex(random_bytes(16)) . '.pdf';
        $caminhoFisico = $pasta . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFisico)) {
            throw new RuntimeException('Não foi possível salvar o PDF.');
        }

        // Caminho relativo à raiz do projeto, usado pelas páginas para abrir o PDF
        $caminhoAnexo = 'uploads/projetos/' . $nomeArquivo;
    }

    $stmt = $conn->prepare("
        INSERT INTO projeto_lei (titulo, ementa, texto, anexo_pdf, id_situacao, id_vereador)
        VALUES (?, ?, ?, ?, 1, ?)
    ");
    $stmt->bind_param('ssssi', $titulo, $ementa, $texto, $caminhoAnexo, $idVereador);
    $stmt->execute();

    $idProjeto = $conn->insert_id;

    $stmt->close();

    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Projeto de lei publicado com sucesso!',
        'id_projeto' => $idProjeto
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    // Se o banco falhou, apaga o PDF que já tinha sido salvo
    if ($caminhoFisico !== null && is_file($caminhoFisico)) {
        unlink($caminhoFisico);
    }

    error_log('Erro ao publicar projeto de lei: ' . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível publicar o projeto de lei.'
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
