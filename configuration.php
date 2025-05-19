<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: configuracoes.php");
    exit;
}

include 'includes/conexao.php';

// Verifica login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'admin') {
    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['senha'])) {
        $usuario = $_POST['usuario'];
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $user = $resultado->fetch_assoc();
            if (password_verify($senha, $user['senha']) && $user['tipo'] === 'admin') {
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['tipo'] = $user['tipo'];
                // Continua carregando a página após login com sucesso
            } else {
                $erro = "Usuário ou senha inválidos!";
            }
        } else {
            $erro = "Usuário não encontrado!";
        }
    }

    // Se não logado, mostra formulário e interrompe o restante
    if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'admin') {
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <title>Login - SelliPort</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #ecf0f1;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .login-container {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px #bdc3c7;
                    width: 100%;
                    max-width: 400px;
                }
                .login-container h2 {
                    text-align: center;
                    margin-bottom: 20px;
                    color: #2c3e50;
                }
                .login-container input {
                    width: 100%;
                    padding: 10px;
                    margin: 10px 0;
                    border: 1px solid #bdc3c7;
                    border-radius: 6px;
                }
                .login-container button {
                    width: 100%;
                    padding: 10px;
                    background-color: #3498db;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                }
                .login-container button:hover {
                    background-color: #2980b9;
                }
                .erro {
                    color: red;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>Login de Administrador</h2>
                <?php if (!empty($erro)): ?>
                    <p class="erro"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>
                <form method="post">
                    <input type="text" name="usuario" placeholder="Usuário" required>
                    <input type="password" name="senha" placeholder="Senha" required>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<!-- Inclui HTML da página de configurações normalmente -->
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Configurações - SelliPort</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Mesmos estilos CSS do produtos.php */<style>
  <?php include 'includes/estilo_padrao.css'; ?>

  /* Estilo específico para a página de Configurações */
  <?php include 'includes/estilo_padrao.css'; ?>
  body {
    background-color: #f5f8fa;
  }

  main {
    padding: 20px;
  }

  h2 {
    margin-bottom: 20px;
    color: #2c3e50;
    font-size: 26px;
    border-left: 6px solid #3498db;
    padding-left: 10px;
  }

  .card {
    background-color: #ffffff;
    border-left: 4px solid #3498db;
    border-radius: 12px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
    padding: 20px;
  }

  .card h3 {
    color: #2980b9;
    margin-bottom: 15px;
  }

  .form-group {
    margin-bottom: 15px;
  }

  .form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #34495e;
  }

  .form-group input,
  .form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #bdc3c7;
    border-radius: 6px;
  }

  button {
    background-color: #3498db;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  button:hover {
    background-color: #2980b9;
  }

  .card form button[style*="background-color:#c0392b"] {
    background-color: #c0392b;
  }

  .card form button[style*="background-color:#c0392b"]:hover {
    background-color: #922b21;
  }

  a button {
    all: unset;
    padding: 10px 18px;
    background-color: #2ecc71;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  a button:hover {
    background-color: #27ae60;
  }
</style>

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
        <a href="configuracoes.php" class="active">Configurações</a>
        <?php if (isset($_SESSION['usuario']) && $_SESSION['tipo'] === 'admin'): ?>
      </nav>      
    <footer>&copy; <?= date('Y') ?> SelliPort</footer>
  </aside>

  <main>
    <h2>Configurações do Sistema</h2>
    <div style="text-align: right; margin-bottom: 20px;">
          <a href="logout.php" style="padding: 8px 14px; background-color:rgb(247, 75, 56); color: white; border-radius: 6px; text-decoration: none;">Sair</a>
        </div>
      <?php endif; ?>
    <div class="card">
      <h3>Informações da Empresa</h3>
      <form method="post">
        <div class="form-group">
          <label for="nome_empresa">Nome da Empresa:</label>
          <input type="text" id="nome_empresa" name="nome_empresa" value="SelliPort Investiment LDA" required>
        </div>
        <div class="form-group">
          <label for="email_contato">Email de Contato:</label>
          <input type="text" id="email_contato" name="email_contato" value="contato@selliport.cv">
        </div>
        <button type="submit" name="salvar_empresa">Salvar</button>
      </form>
    </div>

    <div class="card">
      <h3>Parâmetros do Sistema</h3>
      <form method="post">
        <div class="form-group">
          <label for="moeda">Moeda:</label>
          <input type="text" id="moeda" name="moeda" value="EUR" required>
        </div>
        <div class="form-group">
          <label for="idioma">Idioma:</label>
          <select name="idioma" id="idioma">
            <option value="pt" selected>Português</option>
            <option value="en">Inglês</option>
            <option value="fr">Francês</option>
          </select>
        </div>
        <button type="submit" name="salvar_parametros">Salvar Parâmetros</button>
      </form>
    </div>

    <div class="card">
      <h3>Backup do Banco de Dados</h3>
      <p>Faça download de uma cópia do banco de dados atual:</p>
      <a href="exportar_backup.php"><button>Exportar Backup</button></a>
    </div>

    <div class="card">
      <h3>Reset de Estoque</h3>
      <p><strong>Cuidado:</strong> Esta ação irá zerar todos os estoques de produtos!</p>
      <form method="post" onsubmit="return confirm('Tem certeza que deseja resetar os estoques?');">
        <button type="submit" name="resetar_estoque" style="background-color:#c0392b;">Resetar Estoque</button>
      </form>
    </div>
  </main>
</body>
</html>
