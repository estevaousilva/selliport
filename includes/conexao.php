<?php
$host = 'localhost';
$usuario = 'usuario_mysql';
$senha = 'senha_mysql';
$banco = 'selliport';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>