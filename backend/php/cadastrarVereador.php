<?php 
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$nomeVereador = $dados["nomeV"] ?? '';
$inicioMandato = $dados["inicioMandato"] ?? '';
$fimMandato = $dados["fimMandato"] ?? '';
$descricao = $dados["descricao"] ?? ''; 
$foto = $dados["foto"] ?? ''; 
$partidoVereador = $dados["partidoVereador"] ?? '';

if (empty($nomeVereador) || empty($inicioMandato) || empty($fimMandato) || empty($descricao) || empty($partidoVereador)){
    echo json_encode(["mensagem" => "Preencha todos os campos obrigatórios!"]);
    exit;
}

$sql = "INSERT INTO vereador (nome_parlamentar, data_inicio_mandato, data_termino_mandato, descricao, foto, id_partido) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt){
    echo json_encode(["mensagem" => "Erro na preparação: " . $conn->error]);
    exit;
}


$stmt->bind_param("sssssi", $nomeVereador, $inicioMandato, $fimMandato, $descricao, $foto, $partidoVereador);

if ($stmt->execute()) {
    echo json_encode(["mensagem" => "Vereador cadastrado com sucesso!"]);
} else {
    echo json_encode(["mensagem" => "Falha ao cadastrar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>