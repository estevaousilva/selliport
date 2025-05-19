<?php 
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'selliport';
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'] ?? '';
    $valor = (float) ($_POST['valor'] ?? 0);
    $data_despesa = $_POST['data_despesa'] ?? '';
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';

    if ($descricao && $valor > 0 && $data_despesa && $forma_pagamento) {
        $stmt = $conn->prepare("INSERT INTO despesas (descricao, valor, data_despesa, forma_pagamento) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $descricao, $valor, $data_despesa, $forma_pagamento);
        $stmt->execute();
        $stmt->close();
        header("Location: despesas.php?sucesso=1");
        exit;
    }
}

// Total de vendas
$resVendas = $conn->query("SELECT SUM(valor_total) as total_vendas FROM vendas");
$totalVendas = $resVendas->fetch_assoc()['total_vendas'] ?? 0;

// Total de despesas
$resDespesas = $conn->query("SELECT SUM(valor) as total_despesas FROM despesas");
$totalDespesas = $resDespesas->fetch_assoc()['total_despesas'] ?? 0;

// Lucro
$lucro = $totalVendas - $totalDespesas;


$result = $conn->query("SELECT * FROM despesas ORDER BY data_despesa DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Despesas - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #003366;
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin-bottom: 15px;
        }

        .main {
            margin-left: 240px;
            padding: 30px;
            overflow-y: auto;
            width: calc(100% - 240px);
        }

        .card {
            
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        h2 {
            margin-bottom: 15px;
            color: #003366;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005599;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #003366;
            color: white;
        }

        canvas {
            max-width: 500px;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SelliPort</h2>
    <a href="index.php">← Voltar ao Dashboard</a>
</div>

<div class="main">
    <div class="card">
        <h2>Registrar Nova Despesa</h2>
        <?php if (isset($_GET['sucesso'])): ?>
            <p class="success">Despesa registrada com sucesso!</p>
        <?php endif; ?>

        <form method="POST">
            <label>Descrição:</label>
            <input type="text" name="descricao" required>

            <label>Valor (€):</label>
            <input type="number" step="0.01" name="valor" required>

            <label>Data da Despesa:</label>
            <input type="date" name="data_despesa" required>

            <label>Forma de Pagamento:</label>
            <select name="forma_pagamento" required>
                <option value="">Selecione...</option>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Transferência">Transferência</option>
            </select>

            <button type="submit">Salvar Despesa</button>
        </form>
    </div>
    <div class="card">
    <h2>Gráfico Financeiro</h2>
    <canvas id="graficoPizza" width="400" height="400"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoPizza').getContext('2d');
    const graficoPizza = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Vendas (€)', 'Despesas (€)', 'Lucro (€)'],
            datasets: [{
                data: [<?= $totalVendas ?>, <?= $totalDespesas ?>, <?= $lucro ?>],
                backgroundColor: ['#4CAF50', '#F44336', '#2196F3'],
                borderColor: ['#388E3C', '#D32F2F', '#1976D2'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

    <div class="card">
        <h2>Lista de Despesas</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Valor (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['data_despesa'])) ?></td>
                        <td><?= htmlspecialchars($row['descricao']) ?></td>
                        <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
