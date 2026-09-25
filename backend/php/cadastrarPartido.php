<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexao.php';

// Lê o JSON bruto do corpo da requisição
$dados = json_decode(file_get_contents("php://input"), true);

$nome   = $dados["nome"] ?? '';
$sigla  = $dados["sigla"] ?? '';
$numero = $dados["numero"] ?? '';

if (empty($nome) || empty($sigla) || empty($numero)) {
    echo json_encode(["mensagem" => "Preencha todos os campos!"]);
    exit;
}

// Consulta preparada
$sql = "INSERT INTO partido (nome, sigla, numero) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["mensagem" => "Erro na preparação: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $nome, $sigla, $numero);

if ($stmt->execute()) {
    echo json_encode(["mensagem" => "Partido cadastrado com sucesso!"]);
} else {
    echo json_encode(["mensagem" => "Falha ao cadastrar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>