<?php

header("Content-Type: application/json; charset=UTF-8");

// 1. Permite apenas requisições POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);
    exit;
}

// 2. Verifica se o cidadão está logado
session_start();

if (!isset($_SESSION["id_cidadao"])) {
    http_response_code(401);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Você precisa estar logado para criar uma indicação."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idCidadao = (int) $_SESSION["id_cidadao"];

// 3. Recebe os dados do formulário
$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");

$cep = preg_replace("/\D/", "", $_POST["cep"] ?? "");
$rua = trim($_POST["logradouro"] ?? "");
$numero = trim($_POST["numero"] ?? "");
$bairro = trim($_POST["bairro"] ?? "");
$municipio = trim($_POST["municipio"] ?? "");
$estado = strtoupper(trim($_POST["estado"] ?? ""));
$complemento = trim($_POST["complemento"] ?? "");

// Vereador escolhido pelo cidadão (opcional, RF11). Vazio = nenhum vereador.
$idVereadorTexto = trim($_POST["id_vereador"] ?? "");
$idVereador = null;

// 4. Valida os campos obrigatórios
if (
    $titulo === "" ||
    $descricao === "" ||
    $cep === "" ||
    $numero === "" ||
    $bairro === "" ||
    $municipio === "" ||
    $estado === ""
) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos obrigatórios."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (
    strlen($cep) !== 8 ||
    !preg_match("/^[A-Z]{2}$/", $estado) ||
    mb_strlen($titulo) > 100 ||
    mb_strlen($descricao) > 1000 ||
    mb_strlen($rua) > 150 ||
    mb_strlen($numero) > 50 ||
    mb_strlen($bairro) > 100 ||
    mb_strlen($municipio) > 100 ||
    mb_strlen($complemento) > 100

) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Verifique o CEP, a UF e o tamanho dos campos preenchidos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// O id do vereador, quando enviado, precisa ser um número inteiro positivo
if ($idVereadorTexto !== "") {

    if (!ctype_digit($idVereadorTexto)) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Vereador inválido."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idVereador = (int) $idVereadorTexto;
}

// 5. Valida o anexo opcional
$arquivo = $_FILES["foto_ou_anexo"] ?? null;
$temArquivo = $arquivo !== null &&
    $arquivo["error"] !== UPLOAD_ERR_NO_FILE;

if ($temArquivo) {

    if ($arquivo["error"] !== UPLOAD_ERR_OK) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao receber o anexo."
        ]);
        exit;
    }

    // Limite de 5 MB e arquivos vaxios
    if (
        $arquivo["size"] <= 0 ||
        $arquivo["size"] > 5 * 1024 * 1024 
    ) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O anexo deve ter entre 1 byte e 5 MB."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Verifica o tipo real do arquivo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo = $finfo->file($arquivo["tmp_name"]);

    $tiposPermitidos = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "application/pdf" => "pdf"
    ];

    if (!isset($tiposPermitidos[$tipo])) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Formato de arquivo não permitido."
        ]);
        exit;
    }
}

require_once __DIR__ . "/conexao.php";

// Confere se o vereador escolhido existe (o id pode ter sido alterado no navegador)
if ($idVereador !== null) {

    $stmtVereador = $conn->prepare(
        "SELECT 1 FROM vereador WHERE id_vereador = ? LIMIT 1"
    );
    $stmtVereador->bind_param("i", $idVereador);
    $stmtVereador->execute();
    $stmtVereador->store_result();

    $vereadorExiste = $stmtVereador->num_rows > 0;

    $stmtVereador->close();

    if (!$vereadorExiste) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O vereador selecionado não foi encontrado."
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }
}

$caminhoFisico = null;

try {

    // 6. Inicia a transação
    $conn->begin_transaction();

    // 7. Cadastra a localização
    $sqlLocalizacao = "
        INSERT INTO localizacao (
            cep,
            numero,
            estado,
            municipio,
            bairro,
            rua,
            complemento
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmtLocal = $conn->prepare($sqlLocalizacao);

    $stmtLocal->bind_param(
        "sssssss",
        $cep,
        $numero,
        $estado,
        $municipio,
        $bairro,
        $rua,
        $complemento
    );

    $stmtLocal->execute();

    // Recupera o ID da localização criada
    $idLocalizacao = $conn->insert_id;

    $stmtLocal->close();

    // 8. Salva o anexo, caso exista
    $caminhoAnexo = null;

    if ($temArquivo) {

        $pasta = __DIR__ . "/../../uploads/indicacoes/";

        if (!is_dir($pasta)) {
            if (!mkdir($pasta, 0755, true) && !is_dir($pasta)) {
                throw new RuntimeException(
                    "Não foi possível criar a pasta de anexos."
                );
            }
        }

        $extensao = $tiposPermitidos[$tipo];

        $nomeArquivo = bin2hex(random_bytes(16)) . "." . $extensao;

        $caminhoFisico = $pasta . $nomeArquivo;

        if (!move_uploaded_file(
            $arquivo["tmp_name"],
            $caminhoFisico
        )) {
            throw new RuntimeException(
                "Não foi possível salvar o anexo."
            );
        }

        // Caminho relativo ao diretório do projeto
        $caminhoAnexo = "uploads/indicacoes/" . $nomeArquivo;
    }

    // 9. Busca o status inicial
    $nomeStatus = "Enviada";

    $sqlStatus = "
        SELECT id_status
        FROM status_indicacao
        WHERE nome_do_status = ?
        LIMIT 1
    ";

    $stmtStatus = $conn->prepare($sqlStatus);
    $stmtStatus->bind_param("s", $nomeStatus);
    $stmtStatus->execute();

    $stmtStatus->bind_result($idStatus);

    if (!$stmtStatus->fetch()) {
        $stmtStatus->close();

        throw new RuntimeException(
            "Status inicial não cadastrado."
        );
    }

    $stmtStatus->close();

    // 10. Cadastra a indicação
    $sqlIndicacao = "
        INSERT INTO indicacao (
            titulo,
            descricao,
            foto_ou_anexo,
            data_de_cadastro,
            id_localizacao,
            id_status,
            id_cidadao,
            id_vereador
        )
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)
    ";

    $stmtIndicacao = $conn->prepare($sqlIndicacao);

    // $idVereador pode ser null: o bind_param grava NULL no banco
    $stmtIndicacao->bind_param(
        "sssiiii",
        $titulo,
        $descricao,
        $caminhoAnexo,
        $idLocalizacao,
        $idStatus,
        $idCidadao,
        $idVereador
    );

    $stmtIndicacao->execute();

    $idIndicacao = $conn->insert_id;

    $stmtIndicacao->close();

    // 11. Confirma a transação
    $conn->commit();

    http_response_code(201);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Indicação cadastrada com sucesso!",
        "id_indicacao" => $idIndicacao
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {

    // Desfaz alterações no banco
    $conn->rollback();

    // Remove o arquivo caso o cadastro tenha falhado
    if (
        $caminhoFisico !== null &&
        is_file($caminhoFisico)
    ) {
        unlink($caminhoFisico);
    }

    error_log("Erro ao cadastrar indicação: " . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível cadastrar a indicação."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();