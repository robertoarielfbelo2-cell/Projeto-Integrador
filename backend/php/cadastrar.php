<?php
header("Content-Type: application/json; charset=UTF-8");

$servidor = "localhost";
$usuario  = "root";
$senha    = "";
$banco    = "projetointegrador";
$porta    = 3307; // Porta personalizada do seu MySQL

$conn = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conn->connect_error) {
    echo json_encode([
        "mensagem" => "Erro ao conectar com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);

$nome            = $dados["nome"] ?? '';
$email           = $dados["email"] ?? '';
$telefone        = $dados["telefone"] ?? '';
$data_nascimento = $dados["data_nascimento"] ?? '';
$senha           = $dados["senha"] ?? '';
$cpf             = $dados["cpf"] ?? '';

if (empty($nome) || empty($email) || empty($senha) || empty($cpf)) {
    echo json_encode([
        "mensagem" => "Dados incompletos enviados."
    ]);
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO cidadao (nome, email, telefone, data_nascimento, senha, cpf) VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "mensagem" => "Erro na preparação da consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "ssssss",
    $nome,
    $email,
    $telefone,
    $data_nascimento,
    $senhaHash,
    $cpf
);

if ($stmt->execute()) {
    echo json_encode([
        "mensagem" => "Cidadão cadastrado com sucesso!"
    ]);
} else {
    echo json_encode([
        "mensagem" => "Erro ao cadastrar: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>