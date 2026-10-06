<?php
header("Content-Type: application/json; charset=UTF-8");
echo json_encode(["recebido" => $_POST, "arquivo" => $_FILES]);
?>