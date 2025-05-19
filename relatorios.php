<?php 
include 'includes/conexao.php';

// Buscar estatísticas principais
// Total clientes, produtos, vendas
$total_clientes = $conn->query("SELECT COUNT(*) FROM clientes")->fetch_row()[0];
$total_produtos = $conn->query("SELECT COUNT(*) FROM produtos")->fetch_row()[0];
$total_vendas = $conn->query("SELECT COUNT(*) FROM vendas")->fetch_row()[0];

// Receita total
$total_receita = $conn->query("SELECT IFNULL(SUM(valor_total), 0) FROM vendas")->fetch_row()[0];

// Produtos mais vendidos (top 5)

// Consulta para pegar o top 5 produtos mais vendidos
$sql = "
    SELECT p.nome, SUM(vi.quantidade) AS total_vendido
    FROM vendas_itens vi
    JOIN produtos p ON vi.id_produto = p.id
    GROUP BY p.id, p.nome
    ORDER BY total_vendido DESC
    LIMIT 5
";

$result = $conn->query($sql);
$topProdutos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topProdutos[] = $row;
    }
}

// Vendas por mês para gráfico (últimos 12 meses)
$sql_vendas_mes = "
SELECT DATE_FORMAT(data_venda, '%Y-%m') AS mes, SUM(valor_total) AS total
FROM vendas
WHERE data_venda >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY mes
ORDER BY mes ASC";
$vendas_mes = $conn->query($sql_vendas_mes);

$meses = [];
$valores = [];
while ($row = $vendas_mes->fetch_assoc()) {
    $meses[] = $row['mes'];
    $valores[] = (float)$row['total'];
}

$movimentos_detalhados = [];

$sql_mov = "
    SELECT 
    me.data_movimentacao,
    p.nome AS produto_nome,
    me.tipo,
    me.quantidade
FROM movimentacoes_estoque me
JOIN produtos p ON me.produto_id = p.id
ORDER BY me.data_movimentacao DESC
";

if ($result_mov = $conn->query($sql_mov)) {
    while ($row = $result_mov->fetch_assoc()) {
        $movimentos_detalhados[] = $row;
    }
} else {
    // Exibe erro no SQL (opcional, útil para desenvolvimento)
    echo "<p class='message error'>Erro ao buscar movimentações: " . $conn->error . "</p>";
}


?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8" />
<title>Relatórios - SelliPort</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  /* Mesma estilização do index.php - pode copiar o CSS inteiro do index.php para aqui */
  /* Seu CSS permanece igual - sem necessidade de mudanças aqui */
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

    /* Formulário estilizado */
    form#addProductForm {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 40px;
      justify-content: flex-start;
    }

    form#addProductForm label {
      flex-basis: 100%;
      font-weight: 600;
      margin-bottom: 5px;
      color: #444;
    }

    form#addProductForm .form-group {
      display: flex;
      flex-direction: column;
      width: 220px;
    }

    form#addProductForm input[type="text"],
    form#addProductForm input[type="number"] {
      padding: 10px 12px;
      border: 1.5px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    form#addProductForm input[type="text"]:focus,
    form#addProductForm input[type="number"]:focus {
      border-color: #007bff;
      outline: none;
    }

    form#addProductForm button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      align-self: flex-end;
      margin-top: 24px;
      transition: background-color 0.3s ease;
    }

    form#addProductForm button:hover {
      background-color: #0056b3;
    }

    /* Lista de produtos - tabela */
    table#productList {
      width: 100%;
      border-collapse: collapse;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    table#productList th,
    table#productList td {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
      text-align: left;
      font-size: 1rem;
    }

    table#productList th {
      background-color: #007bff;
      color: white;
      font-weight: 600;
    }

    table#productList tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    table#productList tr:hover {
      background-color: #e6f0ff;
      cursor: default;
    }

    /* Mensagem de sucesso / erro */
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

    /* Responsividade */
    @media (max-width: 768px) {
      aside {
        display: none;
      }
      body {
        flex-direction: column;
      }
      main {
        padding: 15px;
        border-radius: 0;
        box-shadow: none;
      }
      form#addProductForm {
        justify-content: center;
      }
      form#addProductForm .form-group {
        width: 100%;
        max-width: 300px;
      }
    }
    .stats-container {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  margin-top: 20px;
}

.stat-block {
  flex: 1 1 150px;
  background-color: #f1f5ff;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
  transition: transform 0.2s ease;
}

.stat-block h4 {
  font-size: 1rem;
  color: #555;
  margin-bottom: 10px;
}

.stat-block p {
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
  color: #1E3A8A;
}

.stat-block:hover {
  transform: translateY(-4px);
}

.stat-block.clients {
  background-color: #e6f4ea;
  color: #256029;
}
.stat-block.clients p {
  color: #256029;
}

.stat-block.products {
  background-color: #e0f2ff;
  color: #1E3A8A;
}
.stat-block.products p {
  color: #1E3A8A;
}

.stat-block.sales {
  background-color: #fff5e6;
  color: #b76e00;
}
.stat-block.sales p {
  color: #b76e00;
}
table#topProdutos {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  box-shadow: 0 4px 10px rgba(0,0,0,0.07);
}

table#topProdutos thead tr {
  background-color: #1E3A8A;
  color: white;
  text-align: left;
  font-weight: 700;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

table#topProdutos thead tr th {
  padding: 14px 20px;
}

table#topProdutos tbody tr {
  background: #f9fafb;
  transition: background-color 0.3s ease;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

table#topProdutos tbody tr:hover {
  background-color: #e6f0ff;
  cursor: default;
  box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

table#topProdutos tbody td {
  padding: 14px 20px;
  font-size: 1rem;
  color: #1E3A8A;
}

table#topProdutos tbody tr td:first-child {
  font-weight: 600;
}

@media (max-width: 600px) {
  table#topProdutos thead {
    display: none;
  }
  table#topProdutos, 
  table#topProdutos tbody, 
  table#topProdutos tr, 
  table#topProdutos td {
    display: block;
    width: 100%;
  }
  table#topProdutos tr {
    margin-bottom: 15px;
    box-shadow: none;
    border-radius: 8px;
    background: #f9fafb;
    padding: 10px;
  }
  table#topProdutos td {
    text-align: right;
    padding-left: 50%;
    position: relative;
    font-size: 0.9rem;
  }
  table#topProdutos td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    top: 14px;
    font-weight: 600;
    color: #555;
    text-transform: uppercase;
    font-size: 0.75rem;
  }
}

table#movimentoEstoque {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  box-shadow: 0 4px 10px rgba(0,0,0,0.07);
}

table#movimentoEstoque thead tr {
  background-color: #1E3A8A;
  color: white;
  text-align: left;
  font-weight: 700;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

table#movimentoEstoque thead tr th {
  padding: 14px 20px;
}

table#movimentoEstoque tbody tr {
  background: #f9fafb;
  transition: background-color 0.3s ease;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

table#movimentoEstoque tbody tr:hover {
  background-color: #e6f0ff;
  cursor: default;
  box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

table#movimentoEstoque tbody td {
  padding: 14px 20px;
  font-size: 1rem;
  color: #1E3A8A;
}

table#movimentoEstoque tbody tr td:first-child {
  font-weight: 600;
}


  /* ... para manter o exemplo enxuto, estou omitindo o CSS, mas você pode usar o mesmo */
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <a href="relatorios.php" style="background-color:#3749A5;">Relatórios</a>
      <a href="configuration.php">Configurações</a>
    </nav>
  </div>
  <div>
    <p>&copy; <?= date("Y") ?> SelliPort</p>
  </div>
</aside>

<main>
  <div class="header">
    <h2>Relatórios do Sistema</h2>
    <p>Visão geral e análises importantes para o negócio.</p>
  </div>

  <div class="card stats-container">
    <div class="stat-block clients">
      <h4>Clientes</h4>
      <p><?= $total_clientes ?></p>
    </div>
    <div class="stat-block products">
      <h4>Produtos</h4>
      <p><?= $total_produtos ?></p>
    </div>
    <div class="stat-block sales">
      <h4>Vendas</h4>
      <p><?= $total_vendas ?></p>
    </div>
    <div class="stat-block sales" style="background-color:#ffe6e6; color:#b30000;">
      <h4>Receita Total (CV$)</h4>
      <p><?= number_format($total_receita, 2, ',', '.') ?></p>
    </div>
  </div>

  <div class="card">
  <h3>Movimentações Detalhadas do Estoque</h3>
  <?php if (count($movimentos_detalhados) > 0): ?>
    <table id="movimentoEstoque">
      <thead>
        <tr>
          <th>Data</th>
          <th>Produto</th>
          <th>Tipo</th>
          <th>Quantidade</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($movimentos_detalhados as $mov): ?>
          <?php
            $corLinha = $mov['tipo'] === 'entrada' ? 'style="background-color: #e6f9e6;"' : 'style="background-color: #fff8dc;"';
          ?>
          <tr <?= $corLinha ?>>
            <td><?= date('d/m/Y H:i', strtotime($mov['data_movimentacao'])) ?></td>
            <td><?= htmlspecialchars($mov['produto_nome']) ?></td>
            <td><?= ucfirst($mov['tipo']) ?></td>
            <td><?= $mov['quantidade'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Nenhuma movimentação registrada.</p>
  <?php endif; ?>
</div>


  <div class="card">
   <h3>Produtos Mais Vendidos (Top 5)</h3>

  <?php if (count($topProdutos) > 0): ?>
<table id="topProdutos" border="0" cellpadding="0">
  <thead>
    <tr>
      <th>Produto</th>
      <th>Quantidade Vendida</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($topProdutos as $produto): ?>
    <tr>
      <td data-label="Produto"><?= htmlspecialchars($produto['nome']) ?></td>
      <td data-label="Quantidade Vendida"><?= $produto['total_vendido'] ?></td>
    </tr>
  <?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
  <p>Nenhum produto vendido ainda.</p>
<?php endif; ?>
</div>


  <div class="card">
    <h3>Vendas por Mês (Últimos 12 meses)</h3>
    <canvas id="vendasChart" style="max-width: 100%; height: 400px;"></canvas>
  </div>
</main>

<footer>
  &copy; <?= date("Y") ?> SelliPort - Todos os direitos reservados
</footer>

<script>
const ctx = document.getElementById('vendasChart').getContext('2d');
const vendasChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [{
            label: 'Receita Mensal (CV$)',
            data: <?= json_encode($valores) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0,123,255,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return 'CV$ ' + value.toLocaleString('pt-BR');
                  }
                }
            },
            x: {
              ticks: {
                callback: function(value) {
                  // Formatar mês AAAA-MM para MM/AAAA
                  let label = this.getLabelForValue(value);
                  if(label){
                    let parts = label.split('-');
                    return parts[1] + '/' + parts[0];
                  }
                  return label;
                }
              }
            }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
          }
        }
    }
});
</script>

</body>
</html>
