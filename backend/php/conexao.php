<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "projetointegrador";
$porta = 3307;

$conn = new mysqli($servidor,$usuario,$senha,$banco,$porta);

if ($conn->connect_error){
    echo json_encode(["mensagem"=>"Erro ao conectar: " . $conn->connect_error]);
    exit;
}
?>