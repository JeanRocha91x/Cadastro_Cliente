<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
if ($_POST) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $plano = $_POST['plano'];
    $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
    $data_inicio = $_POST['data_inicio'];
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, plano, valor, data_inicio) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $telefone, $plano, $valor, $data_inicio]);
    header('Location: index.php?msg=Cliente cadastrado!');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- HAMBURGUER MOBILE -->
    <div class="hamburger" onclick="toggleSidebar()">
        <span></span><span></span><span></span>
    </div>

    <div class="sidebar">
        <div class="logo">Sistema X</div>
        <a href="index.php"><i class="material-icons">dashboard</i> Dashboard</a>
        <a href="add.php" class="active"><i class="material-icons">person_add</i> Novo Cliente</a>
        <a href="relatorio.php"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
        <a href="export.php"><i class="material-icons">download</i> Exportar</a>
        <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
    </div>
    <div class="main">
        <header>
            <h1>Novo Cliente</h1>
            <a href="index.php" class="btn-back">Voltar</a>
        </header>
        <form method="POST" class="payment-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Telefone *</label>
                    <input type="text" name="telefone" placeholder="(00) 00000-0000" required>
                </div>
                <div class="form-group">
                    <label>Plano *</label>
                    <select name="plano" required>
                        <option value="mensal">Mensal</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor (R$) *</label>
                    <input type="text" name="valor" placeholder="99,90" required>
                </div>
                <div class="form-group">
                    <label>Data de Início *</label>
                    <input type="date" name="data_inicio" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <button type="submit" class="btn">Salvar Cliente</button>
        </form>
    </div>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
    </script>
    <script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}
</script>
</body>
</html>