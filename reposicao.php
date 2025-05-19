<?php
include 'includes/conexao.php';

$mensagem = "";
$mensagem_class = "";

if (!isset($_GET['produto_id']) || !is_numeric($_GET['produto_id'])) {
    die("Produto inválido.");
}

$produto_id = (int) $_GET['produto_id'];

// Buscar dados do produto
$stmt = $conn->prepare("SELECT nome, quantidade FROM produtos WHERE id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();
$stmt->close();

if (!$produto) {
    die("Produto não encontrado.");
}

// Lidar com o POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nova_quantidade'])) {
    $nova_quantidade = (int) $_POST['nova_quantidade'];

    if ($nova_quantidade > 0) {
        // Atualizar estoque
        $stmt = $conn->prepare("UPDATE produtos SET quantidade = quantidade + ?, data_movimento = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $nova_quantidade, $produto_id);
        $stmt->execute();
        $stmt->close();

        // Registrar movimentação
        $stmt = $conn->prepare("INSERT INTO movimentacoes_estoque (produto_id, quantidade, tipo, data_movimentacao) VALUES (?, ?, 'entrada', NOW())");
        $stmt->bind_param("ii", $produto_id, $nova_quantidade);
        $stmt->execute();
        $stmt->close();

        $mensagem = "Estoque do produto atualizado com sucesso!";
        $mensagem_class = "success";

        // Atualizar os dados do produto na tela
        $produto['quantidade'] += $nova_quantidade;
    } else {
        $mensagem = "Quantidade inválida para reposição.";
        $mensagem_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Reposição de Produto</title>
  <style>
    body {
      background: #f4f6f8;
      font-family: Arial, sans-serif;
      padding: 40px;
    }

    .card {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      margin-bottom: 20px;
      color: #003366;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
    }

    input[type="number"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    button {
      background-color: #003366;
      color: #fff;
      border: none;
      padding: 12px 24px;
      font-size: 1em;
      border-radius: 8px;
      cursor: pointer;
    }

    .message {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      font-weight: bold;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

    a.back {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #003366;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Reposição de Estoque</h2>

    <?php if ($mensagem): ?>
      <div class="message <?= $mensagem_class ?>">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <p><strong>Produto:</strong> <?= htmlspecialchars($produto['nome']) ?></p>
    <p><strong>Quantidade Atual:</strong> <?= (int)$produto['quantidade'] ?></p>

    <form method="post">
      <div class="form-group">
        <label for="nova_quantidade">Quantidade a adicionar:</label>
        <input type="number" name="nova_quantidade" id="nova_quantidade" min="1" required>
      </div>
      <button type="submit">Repor Estoque</button>
    </form>

    <a class="back" href="produtos.php">← Voltar para Produtos</a>
  </div>
</body>
</html>
