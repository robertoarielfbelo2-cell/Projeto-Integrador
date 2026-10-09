<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexao.php';

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$cep = preg_replace('/\D/', '', $cep);
$numero = trim($_POST['numero'] ?? '');
$numero = $numero !== '' ? $numero : null;
$estado = trim($_POST['estado'] ?? '');
$municipio = trim($_POST['municipio'] ?? '');
$bairro = trim($_POST['bairro'] ?? '');
$rua = trim($_POST['rua'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');

if(empty($titulo) || empty($descricao) || empty($cep) || empty($estado)
    || empty($municipio) || empty($bairro) || empty($rua)) {
        echo json_encode ([
            'sucesso' => false,
            'mensagem' => 'Preencha todos os campos obrigatórios.'
        ]);
        exit;
    }

$sql = "INSERT INTO localizacao (cep, numero, estado, municipio, bairro, rua, complemento)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn-> prepare($sql);
$stmt->bind_param("sssssss", $cep, $numero, $estado, $municipio, $bairro, $rua, $complemento);
$stmt->execute();
$id_localizacao = $conn -> insert_id;

$id_status = 1;
$id_cidadao = 1;
$foto_ou_anexo = null;

$sql = "INSERT INTO indicacao (titulo, descricao, foto_ou_anexo, id_localizacao, id_status, id_cidadao)
    VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn-> prepare($sql);
$stmt->bind_param("sssiii", $titulo, $descricao, $foto_ou_anexo, $id_localizacao, $id_status, $id_cidadao);
$id_localizacao = $conn -> insert_id;

if ($stmt->execute()) {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Indicação cadastrada com sucesso.'
    ]);
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao cadastrar: ' . $stmt->error
    ]);
}

$conn->close();
