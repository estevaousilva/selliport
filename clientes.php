<?php
include 'includes/conexao.php';

$mensagem = "";
$mensagem_class = "";

// Processa a submissão do formulário de novo cliente
if (isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $morada = trim($_POST['morada']);
    $telefone = trim($_POST['telefone']);

    // Validações simples
    if ($nome === "") {
        $mensagem = "O nome é obrigatório.";
        $mensagem_class = "error";
    } else {
        // Inserir novo cliente
        $stmt = $conn->prepare("INSERT INTO clientes (nome, morada, telefone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $morada, $telefone);
        if ($stmt->execute()) {
            $mensagem = "Cliente adicionado com sucesso!";
            $mensagem_class = "success";
        } else {
            $mensagem = "Erro ao adicionar cliente: " . $conn->error;
            $mensagem_class = "error";
        }
        $stmt->close();
    }
}

$clientes = [];
$sql_clientes = "SELECT id, nome, morada, telefone FROM clientes ORDER BY nome ASC";
$result_clientes = $conn->query($sql_clientes);

if ($result_clientes && $result_clientes->num_rows > 0) {
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

$stmt_produto = $conn->prepare("SELECT preco FROM produtos WHERE id = ?");
if (!$stmt_produto) {
    die("Erro na preparação da query: " . $conn->error);
}

$stmt_produto = $conn->prepare("SELECT preco FROM produtos WHERE id = ?");
if (!$stmt_produto) {
    die("Erro na preparação da query: " . $conn->error);
}
$stmt_produto->bind_param("i", $produto_id);
$stmt_produto->execute();
$stmt_produto->bind_result($preco);
$stmt_produto->fetch();
$stmt_produto->close();

?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Clientes - SelliPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    /* Manter o estilo que você já tinha */
    /* ... (mantém o CSS do seu código original, com ajustes apenas para "Clientes") */
    
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


    main {
      flex: 1;
      padding: 20px 30px;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      overflow-x: auto;
    }

    h2 {
      margin-top: 0;
      color: #004aad;
    }

    .card {
      background: #f1f7ff;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 2px 5px rgba(0, 74, 173, 0.15);
    }

    form .form-group {
      margin-bottom: 15px;
    }

    form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #004aad;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="tel"] {
      width: 100%;
      padding: 8px 10px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border-color 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="email"]:focus,
    form input[type="tel"]:focus {
      border-color: #004aad;
      outline: none;
    }

    form button {
      background-color: #004aad;
      border: none;
      padding: 10px 18px;
      color: white;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    form button:hover,
    form button:focus {
      background-color: #063d9a;
      outline: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    table thead {
      background-color: #004aad;
      color: white;
    }

    table th,
    table td {
      padding: 10px 12px;
      border: 1px solid #ddd;
      text-align: left;
    }

    table tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .message {
      padding: 10px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
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
  </style>
</head>
<body>
  <aside>
    <div>
      <h1>SelliPort Investiment LDA</h1>
      <nav>
        <a href="index.php">Dashboard</a>
        <a href="clientes.php" class="active">Clientes</a>
        <a href="produtos.php">Produtos</a>
        <a href="vendas.php">Vendas</a>
        <a href="despesas.php">Registos de Gastos</a>
        <a href="relatorios.php">Relatórios</a>
        <a href="configuration.php">Configurações</a>
      </nav>
    </div>
    <div>
      <p>&copy; <?php echo date("Y"); ?> SelliPort</p>
    </div>
  </aside>

  <main>
    <h2>Clientes</h2>

    <?php if ($mensagem): ?>
      <div class="message <?= $mensagem_class ?>">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h3>Adicionar Cliente</h3>
      <form action="" method="post" autocomplete="off" id="formCliente">
        <div class="form-group">
          <label for="nome">Nome:</label>
          <input type="text" id="nome" name="nome" required />
        </div>
        <div class="form-group">
          <label for="email">Morada:</label>
          <input type="text" id="morada" name="morada" />
        </div>
        <div class="form-group">
          <label for="telefone">Telefone:</label>
          <input type="tel" id="telefone" name="telefone" />
        </div>
        <button type="submit" name="adicionar">Adicionar Cliente</button>
      </form>
    </div>

    <div class="card">
      <h3>Lista de Clientes</h3>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Morada</th>
            <th>Telefone</th>
          </tr>
        </thead>
        <tbody>
  <?php if (count($clientes) > 0): ?>
    <?php foreach ($clientes as $cliente): ?>
      <tr>
        <td><?= htmlspecialchars($cliente['nome']) ?></td>
        <td><?= htmlspecialchars($cliente['morada']) ?: '-' ?></td>
        <td><?= htmlspecialchars($cliente['telefone']) ?: '-' ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="3" style="text-align:center;">Nenhum cliente encontrado.</td></tr>
  <?php endif; ?>
</tbody>

      </table>
    </div>
  </main>
</body>
</html>
