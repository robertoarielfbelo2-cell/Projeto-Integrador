<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexao.php';

// O 'AS id' renomeia a coluna id_partido para 'id' no retorno em JSON
$sql = "SELECT id_partido AS id, nome, sigla FROM partido ORDER BY nome ASC";
$result = $conn->query($sql);

$partidos = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $partidos[] = $row;
    }
}

echo json_encode($partidos);
$conn->close();
?>