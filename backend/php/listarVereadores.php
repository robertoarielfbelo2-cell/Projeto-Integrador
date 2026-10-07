<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexao.php';

$sql = "SELECT v.id_vereador AS id, TRIM(v.nome_parlamentar) AS nome, v.descricao, v.foto, p,sigla FROM vereaador v JOIN partido p ON p.id_partido = v.id_partido ORDER BY TRIM(v.nome_parlamentar) ASC";

$result = $conn->query($sql);

$vereadores = array();
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $vereadores[] = $row;
    }
}

echo json_encode($vereadores);
$conn->close();
?>