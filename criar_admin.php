<?php
include 'includes/conexao.php';

$nome = "Administrador";
$usuario = "admin";
$senha = "admin123"; // Pode trocar por algo mais forte!
$tipo = "admin";

// Criptografa a senha com segurança
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Insere no banco
$stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, senha, tipo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $usuario, $senhaHash, $tipo);

if ($stmt->execute()) {
    echo "Usuário administrador criado com sucesso!";
} else {
    echo "Erro: " . $stmt->error;
}
?>
