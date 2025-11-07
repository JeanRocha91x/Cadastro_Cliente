<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();
if (!$cliente) {
    header('Location: index.php');
    exit;
}
if ($_POST) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $plano = $_POST['plano'];
    $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
    $data_inicio = $_POST['data_inicio'];
    $stmt = $pdo->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, plano=?, valor=?, data_inicio=? WHERE id=?");
    $stmt->execute([$nome, $email, $telefone, $plano, $valor, $data_inicio, $id]);
    header("Location: index.php?msg=atualizado");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente: <?= htmlspecialchars($cliente['nome']) ?></title>
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
        <a href="add.php"><i class="material-icons">person_add</i> Novo Cliente</a>
        <a href="relatorio.php"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
        <a href="export.php"><i class="material-icons">download</i> Exportar</a>
        <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
    </div>
    <div class="main">
        <header>
            <h1>Editar Cliente: <?= htmlspecialchars($cliente['nome']) ?></h1>
            <a href="index.php" class="btn-back">Voltar</a>
        </header>
        <form method="POST" class="payment-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Telefone *</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Plano *</label>
                    <select name="plano" required>
                        <option value="mensal" <?= $cliente['plano'] == 'mensal' ? 'selected' : '' ?>>Mensal</option>
                        <option value="trimestral" <?= $cliente['plano'] == 'trimestral' ? 'selected' : '' ?>>Trimestral</option>
                        <option value="semestral" <?= $cliente['plano'] == 'semestral' ? 'selected' : '' ?>>Semestral</option>
                        <option value="anual" <?= $cliente['plano'] == 'anual' ? 'selected' : '' ?>>Anual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor (R$) *</label>
                    <input type="text" name="valor" value="<?= number_format($cliente['valor'], 2, ',', '.') ?>" required>
                </div>
                <div class="form-group">
                    <label>Data de Início *</label>
                    <input type="date" name="data_inicio" value="<?= $cliente['data_inicio'] ?>" required>
                </div>
            </div>
            <button type="submit" class="btn">Atualizar Cliente</button>
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