<?php
session_start(); // Importante para mensagens após redirecionamento
include 'includes/conexao.php';

$mensagem = "";
$mensagem_class = "";

// Verifica se há mensagem de sessão (após redirecionamento)
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $mensagem_class = $_SESSION['mensagem_class'];
    unset($_SESSION['mensagem'], $_SESSION['mensagem_class']);
}

// Se formulário for enviado
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nome'], $_POST['preco'], $_POST['quantidade'])) {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];

    // Inserir na tabela produtos
    $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, quantidade, data_movimento) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sdi", $nome, $preco, $quantidade);

    if ($stmt->execute()) {
        $produto_id = $conn->insert_id;

        // Registrar movimentação
        $stmt_mov = $conn->prepare("INSERT INTO movimentacoes_estoque (produto_id, quantidade, tipo, data_movimentacao) VALUES (?, ?, 'entrada', NOW())");
        $stmt_mov->bind_param("ii", $produto_id, $quantidade);
        $stmt_mov->execute();
        $stmt_mov->close();

        // Define mensagem na sessão e redireciona
        $_SESSION['mensagem'] = "Produto adicionado com sucesso!";
        $_SESSION['mensagem_class'] = "success";
        header("Location: produtos.php");
        exit();
    } else {
        $mensagem = "Erro ao adicionar produto: " . $conn->error;
        $mensagem_class = "error";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Produtos - SelliPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
  /* Seu CSS permanece o mesmo, omitido aqui para foco no PHP */

  * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f4f6f8;
      margin: 0;
      padding: 20px;
      color: #333;
      display: flex;
      min-height: 100vh;
    }

    aside {
      width: 250px;
      background-color: #1E3A8A;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    aside h1 {
      font-size: 24px;
      margin-bottom: 30px;
    }

    nav a {
      display: block;
      color: #fff;
      text-decoration: none;
      margin-bottom: 15px;
      font-size: 16px;
      padding: 8px;
      border-radius: 5px;
      transition: background-color 0.2s ease;
    }

    nav a:hover {
      background-color: #3749A5;
    }

  footer {
    font-size: 0.9em;
    text-align: center;
    color: #aabbcc;
    margin-top: 20px;
  }

  main {
    flex: 1;
    padding: 40px;
  }

  h2 {
    font-size: 1.8em;
    color: #003366;
    margin-bottom: 25px;
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 30px;
    margin-bottom: 30px;
  }

  .card h3 {
    font-size: 1.4em;
    margin-bottom: 20px;
    color: #002244;
  }

  .form-group {
    margin-bottom: 18px;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
  }

  input[type="text"],
  input[type="number"] {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1em;
    transition: 0.2s ease;
  }

  input[type="text"]:focus,
  input[type="number"]:focus {
    border-color: #0077cc;
    outline: none;
  }

  button {
    background-color: #003366;
    color: white;
    border: none;
    padding: 12px 24px;
    font-size: 1em;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  button:hover {
    background-color: #0059b3;
  }

  .message {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: bold;
  }

  .success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  thead {
    background-color: #003366;
    color: white;
  }

  th, td {
    text-align: left;
    padding: 14px;
    border-bottom: 1px solid #ddd;
  }

  tbody tr:nth-child(even) {
    background-color: #f1f1f1;
  }

  tbody tr:hover {
    background-color: #e9f5ff;
  }

  @media (max-width: 768px) {
    aside {
      display: none;
    }

    main {
      padding: 20px;
    }

    .card {
      padding: 20px;
    }

    table, thead, tbody, th, td, tr {
      display: block;
    }

    thead {
      display: none;
    }

    td {
      position: relative;
      padding-left: 50%;
      text-align: right;
      border: none;
    }

    td::before {
      position: absolute;
      left: 10px;
      width: 45%;
      white-space: nowrap;
      font-weight: bold;
      color: #555;
    }

    td:nth-of-type(1)::before { content: "Nome"; }
    td:nth-of-type(2)::before { content: "Preço (EUR)"; }
    td:nth-of-type(3)::before { content: "Quantidade"; }
  }
  .estoque-zero {
  background-color: #f8d7da !important;
  color: #721c24;
}

.estoque-baixo {
  background-color: #fff3cd !important;
  color: #856404;
}
.estoque-ok {
  background-color: #d4edda !important;
  color: #155724;
}


  </style>
</head>
<body>
  <aside>
    <div>
      <h1>SelliPort Investiment LDA</h1>
      <nav>
        <a href="index.php">Dashboard</a>
        <a href="clientes.php">Clientes</a>
        <a href="produtos.php" class="active">Produtos</a>
        <a href="vendas.php">Vendas</a>
        <a href="despesas.php">Registos de Gastos</a>
        <a href="relatorios.php">Relatórios</a>
        <a href="configuration.php">Configurações</a>
      </nav>
    </div>
    <footer>&copy; <?= date('Y') ?> SelliPort</footer>
  </aside>

  <main>
    <h2>Produtos</h2>

    <?php if ($mensagem): ?>
      <div class="message <?= htmlspecialchars($mensagem_class) ?>">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h3>Adicionar Produto</h3>
      <form method="post" autocomplete="off">
        <div class="form-group">
          <label for="nome">Nome:</label>
          <input type="text" id="nome" name="nome" required>
        </div>
        <div class="form-group">
          <label for="preco">Preço:</label>
          <input type="text" id="preco" name="preco" required>
        </div>
        <div class="form-group">
          <label for="quantidade">Quantidade:</label>
          <input type="number" id="quantidade" name="quantidade" min="0" required />
        </div>
        <button type="submit" name="adicionar">Adicionar</button>
      </form>
    </div>

    <div class="card">
      <h3>Lista de Produtos</h3>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Preço (EUR)</th>
            <th>Quantidade</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
  <?php
  $sql = "SELECT id, nome, preco, quantidade FROM produtos ORDER BY id DESC";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classe_estoque = '';
        $botao_estoque = '';

        // Estilo por quantidade
        if ($row['quantidade'] == 0) {
            $classe_estoque = 'style="background-color: #f8d7da;"'; // vermelho
            $botao_estoque = '<a href="reposicao.php?produto_id=' . $row['id'] . '" style="color: white; background: #dc3545; padding: 6px 10px; border-radius: 6px; text-decoration: none;">⚠ Repor   .</a>';
        } elseif ($row['quantidade'] <= 3) {
            $classe_estoque = 'style="background-color: #fff3cd;"'; // amarelo
            $botao_estoque = '<a href="reposicao.php?produto_id=' . $row['id'] . '" style="color: #856404; background: #ffeeba; padding: 6px 10px; border-radius: 6px; text-decoration: none;">+ Estoque</a>';
        } else {
            $classe_estoque = 'style="background-color: #d4edda;"'; // verde
            $botao_estoque = '<a href="reposicao.php?produto_id=' . $row['id'] . '" style="color: #155724; background: #c3e6cb; padding: 6px 10px; border-radius: 6px; text-decoration: none;">+ Estoque</a>';
        }

        echo "<tr $classe_estoque>";
        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
        echo "<td>" . number_format($row['preco'], 2, ',', '.') . "</td>";
        echo "<td>" . (int)$row['quantidade'] . "</td>";
        echo "<td>$botao_estoque</td>";
        echo "</tr>";
    }
}

   else {
      echo '<tr><td colspan="4" style="text-align:center;">Nenhum produto encontrado.</td></tr>';
  }
  ?>
</tbody>



      </table>
    </div>
  </main>
</body>
</html>
