<?php
// fatura.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'selliport';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$id_venda = (int)($_GET['id'] ?? 0);
if ($id_venda <= 0) {
    die("ID da venda inválido.");
}

$stmt = $conn->prepare("
    SELECT 
        v.id AS id_venda,
        v.data_venda,
        v.valor_total,
        c.nome AS cliente,
        c.telefone,
        c.morada,
        p.nome AS nome_produto,
        p.preco AS preco_unitario,
        vi.quantidade
    FROM vendas v
    JOIN clientes c ON v.id_cliente = c.id
    JOIN vendas_itens vi ON vi.id_venda = v.id
    JOIN produtos p ON vi.id_produto = p.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $id_venda);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Venda não encontrada.");
}

$itens = [];
$cliente = null;
$data_venda = '';
$valor_total = 0;

while ($row = $result->fetch_assoc()) {
    if (!$cliente) {
        $cliente = [
            'nome' => $row['cliente'],
            'telefone' => $row['telefone'],
            'morada' => $row['morada']
        ];
        $data_venda = $row['data_venda'];
        $valor_total = $row['valor_total'];
    }

    $itens[] = [
        'produto' => $row['nome_produto'],
        'preco_unitario' => $row['preco_unitario'],
        'quantidade' => $row['quantidade'],
        'subtotal' => $row['preco_unitario'] * $row['quantidade']
    ];
}

$stmt->close();

// Prepara WhatsApp
$numeroLimpo = preg_replace('/[^0-9]/', '', $cliente['telefone']);
if (!str_starts_with($numeroLimpo, '238')) {
    $telefone = '238' . $numeroLimpo;
} else {
    $telefone = $numeroLimpo;
}

$linkFatura = "http://localhost/selliport/fatura.php?id=" . $id_venda;
$mensagem = urlencode("Olá, segue a sua fatura da SelliPort: $linkFatura");

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Fatura #<?= $id_venda ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        h2 {
            margin-top: 0;
        }
        .dados-cliente, .info-venda {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #005599;
        }
        .dados-empresa {
            background-color: #002b4f;
            color: #fff;
            padding: 20px 25px;
            border-left: 8px solid #00aaff;
            border-radius: 6px;
            margin-bottom: 30px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
        .empresa-nome {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .empresa-endereco,
        .empresa-contacto {
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <a href="#" class="btn" onclick="window.print()">🖨️ Imprimir Fatura</a>
    <a href="https://wa.me/<?= $telefone ?>?text=<?= $mensagem ?>" class="btn" target="_blank">📤 Enviar no WhatsApp</a>

    <h2>Fatura #<?= $id_venda ?></h2>

    <div class="dados-empresa">
        <div class="empresa-nome">SelliPort Investiment LDA</div>
        <div class="empresa-endereco">Achada Santo António</div>
        <div class="empresa-contacto">📞 9756066 / 5382251</div>
    </div>

    <div class="dados-cliente">
        <strong>Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?><br>
        <strong>Telefone:</strong> <?= htmlspecialchars($cliente['telefone']) ?><br>
        <strong>Morada:</strong> <?= htmlspecialchars($cliente['morada']) ?><br>
    </div>

    <div class="info-venda">
        <strong>Data da Venda:</strong> <?= date('d/m/Y H:i', strtotime($data_venda)) ?><br>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['produto']) ?></td>
                    <td><?= (int)$item['quantidade'] ?></td>
                    <td><?= number_format($item['preco_unitario'], 2, ',', '.') ?> CVE</td>
                    <td><?= number_format($item['subtotal'], 2, ',', '.') ?> CVE</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Total: <?= number_format($valor_total, 2, ',', '.') ?> CVE
    </div>

</body>
</html>
