<?php
// vendas.php
// Conexão com o banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'selliport';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$mensagem = '';
$mensagem_class = '';

// Buscar clientes e produtos
$clientes = $conn->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$produtos = $conn->query("SELECT id, nome, preco FROM produtos ORDER BY nome ASC");

// Lógica de inserção da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'])) {
    $id_cliente = (int)($_POST['id_cliente'] ?? 0);
    $produto_ids = $_POST['produto_ids'] ?? [];
    $quantidades = $_POST['quantidades'] ?? [];

    if ($id_cliente > 0 && count($produto_ids) > 0 && count($produto_ids) === count($quantidades)) {
        $valor_total = 0.0;
        $produtos_validos = [];

        foreach ($produto_ids as $index => $prod_id) {
            $prod_id = (int)$prod_id;
            $qtd = (int)$quantidades[$index];
            if ($prod_id > 0 && $qtd > 0) {
                $stmt = $conn->prepare("SELECT preco, quantidade FROM produtos WHERE id = ?");
                $stmt->bind_param("i", $prod_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows === 1) {
                    $row = $res->fetch_assoc();
                    $preco = (float)$row['preco'];
                    $estoque_atual = (int)$row['quantidade'];
                    if ($estoque_atual >= $qtd) {
                        $valor_total += $preco * $qtd;
                        $produtos_validos[] = ['id' => $prod_id, 'quantidade' => $qtd];
                    }
                }
                $stmt->close();
            }
        }

        if (count($produtos_validos) > 0 && $valor_total > 0) {
            $data_venda = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO vendas (id_cliente, valor_total, data_venda) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $id_cliente, $valor_total, $data_venda);

            if ($stmt->execute()) {
                $id_venda = $stmt->insert_id;
                $stmt->close();

                $stmt_item = $conn->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade) VALUES (?, ?, ?)");
                $stmt_mov = $conn->prepare("INSERT INTO movimentacoes_estoque (produto_id, quantidade, tipo, data_movimentacao) VALUES (?, ?, 'saida', NOW())");
                $stmt_atualiza = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");

                foreach ($produtos_validos as $item) {
                    $stmt_item->bind_param("iii", $id_venda, $item['id'], $item['quantidade']);
                    $stmt_item->execute();

                    $stmt_mov->bind_param("ii", $item['id'], $item['quantidade']);
                    $stmt_mov->execute();

                    $stmt_atualiza->bind_param("ii", $item['quantidade'], $item['id']);
                    $stmt_atualiza->execute();
                }

                $stmt_item->close();
                $stmt_mov->close();
                $stmt_atualiza->close();

                $mensagem = "Venda registrada com sucesso e estoque atualizado!";
                $mensagem_class = "success";
            } else {
                $mensagem = "Erro ao registrar a venda: " . $conn->error;
                $mensagem_class = "error";
            }
        } else {
            $mensagem = "Produtos inválidos ou estoque insuficiente.";
            $mensagem_class = "error";
        }
    } else {
        $mensagem = "Por favor, selecione um cliente e produtos válidos.";
        $mensagem_class = "error";
    }
}

// Consulta de vendas (sempre executada)
$vendas = $conn->query("
    SELECT v.id, c.nome AS cliente, v.valor_total, v.data_venda
    FROM vendas v
    JOIN clientes c ON v.id_cliente = c.id
    ORDER BY v.data_venda DESC
");
?>


<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Vendas - SelliPort Investiment LDA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    /* Estilos gerais e layout compartilhado */
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
      padding: 30px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
    }
    .header {
      margin-bottom: 20px;
    }
    .card {
      background: #f9fafb;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    footer {
      text-align: center;
      font-size: 13px;
      color: #aaa;
      margin-top: auto;
      padding-top: 20px;
    }
    /* Estilos específicos para o formulário de vendas */
    form#formVenda {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 30px;
    }
    form#formVenda label {
      font-weight: 600;
      color: #444;
    }
    form#formVenda select,
    form#formVenda input[type=number] {
      padding: 10px 12px;
      border: 1.5px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    form#formVenda select:focus,
    form#formVenda input[type=number]:focus {
      border-color: #007bff;
      outline: none;
    }
    form#formVenda button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      align-self: flex-start;
      transition: background-color 0.3s ease;
    }
    form#formVenda button:hover {
      background-color: #0056b3;
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
      padding: 12px 20px;
      border-radius: 6px;
      font-weight: 600;
      display: none;
    }
    .message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1.5px solid #c3e6cb;
      display: block;
    }
    .message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1.5px solid #f5c6cb;
      display: block;
    }
    /* Lista de produtos na venda - estilo similar aos cards */
    .produto-item {
      border: 1px solid #ddd;
      padding: 10px;
      margin-bottom: 10px;
      background: #fefefe;
      border-radius: 4px;
      position: relative;
    }
    .removerProdutoBtn {
      background-color: #dc3545;
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 12px;
      padding: 2px 6px;
      border: none;
      color: white;
      border-radius: 3px;
      cursor: pointer;
    }
    .removerProdutoBtn:hover {
      background-color: #c82333;
    }
    /* Tabela de vendas */
    table {
      width: 100%;
      border-collapse: collapse;
      box-shadow: 0 0 12px rgba(0,0,0,0.05);
    }
    table th,
    table td {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
      text-align: left;
      font-size: 1rem;
    }
    table th {
      background-color: #007bff;
      color: white;
      font-weight: 600;
    }
    table tr:nth-child(even) {
      background-color: #f9fafb;
    }
    table tr:hover {
      background-color: #e6f0ff;
    }
    /* Responsividade */
    @media (max-width: 768px) {
      aside { display: none; }
      body { flex-direction: column; }
      main {
        padding: 15px;
        border-radius: 0;
        box-shadow: none;
      }
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
    <div class="header">
      <h2>Vendas</h2>
      <p>Registro e acompanhamento das vendas realizadas.</p>
    </div>

    <?php if ($mensagem): ?>
      <div class="message <?= $mensagem_class ?>">
          <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h3>Registrar Nova Venda</h3>
      <form method="post" autocomplete="off" id="formVenda">
        <label for="id_cliente">Cliente:</label>
        <select name="id_cliente" id="id_cliente" required>
          <option value="">-- Selecionar Cliente --</option>
          <?php
          if ($clientes && $clientes->num_rows > 0) {
              while ($cli = $clientes->fetch_assoc()) {
                  echo "<option value='" . (int)$cli['id'] . "'>" . htmlspecialchars($cli['nome']) . "</option>";
              }
          }
          ?>
        </select>

        <div id="produtosVendaContainer">
          <div class="produto-item">
            <label>Produto:</label>
            <select name="produto_ids[]" required>
              <option value="">-- Selecionar Produto --</option>
              <?php
              if ($produtos && $produtos->num_rows > 0) {
                  $produtos->data_seek(0);
                  while ($prod = $produtos->fetch_assoc()) {
                      echo "<option value='" . (int)$prod['id'] . "'>" . htmlspecialchars($prod['nome']) . " (CV$ " . number_format($prod['preco'], 2, ',', '.') . ")</option>";
                  }
              }
              ?>
            </select>

            <label>Quantidade:</label>
            <input type="number" name="quantidades[]" min="1" value="1" required />

            <button type="button" class="removerProdutoBtn" title="Remover produto">X</button>
          </div>
        </div>

        <button type="button" id="addProdutoBtn">Adicionar Produto</button>
        <br/>
        <button type="submit" name="adicionar">Registrar Venda</button>
      </form>
    </div>

    <div class="card">
      <h3>Lista de Vendas</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Valor Total (CV$)</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($vendas && $vendas->num_rows > 0) {
              while ($v = $vendas->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . (int)$v['id'] . "</td>";
                  echo "<td>" . htmlspecialchars($v['cliente']) . "</td>";
                  echo "<td>" . number_format($v['valor_total'], 2, ',', '.') . "</td>";
                  echo "<td>" . htmlspecialchars($v['data_venda']) . "</td>";
                  echo "<td><a href='fatura.php?id=" . htmlspecialchars($v['id']) . "' target='_blank' style='padding:6px 12px; background:#003366; color:white; text-decoration:none; border-radius:5px;'>Fatura</a></td>";
                  echo "</tr>";
              }
          } else {
              echo '<tr><td colspan="4" style="text-align:center;">Nenhuma venda encontrada.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>

    <footer>
      &copy; <?php echo date("Y"); ?> SelliPort - Todos os direitos reservados
    </footer>
  </main>

  <script>
    const addProdutoBtn = document.getElementById('addProdutoBtn');
    const produtosVendaContainer = document.getElementById('produtosVendaContainer');

    addProdutoBtn.addEventListener('click', () => {
      const primeiroProduto = produtosVendaContainer.querySelector('.produto-item');
      const novoProduto = primeiroProduto.cloneNode(true);

      // Resetar os valores do novo item
      novoProduto.querySelector('select').value = '';
      novoProduto.querySelector('input[type=number]').value = 1;

      produtosVendaContainer.appendChild(novoProduto);
    });

    produtosVendaContainer.addEventListener('click', e => {
      if (e.target.classList.contains('removerProdutoBtn')) {
        const itens = produtosVendaContainer.querySelectorAll('.produto-item');
        if (itens.length > 1) {
          e.target.closest('.produto-item').remove();
        } else {
          alert('Deve ter pelo menos um produto na venda.');
        }
      }
    });
  </script>
</body>
</html>
