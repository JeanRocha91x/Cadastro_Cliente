<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) { header('Location: index.php'); exit; }

if ($_POST) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $plano = $_POST['plano'];
    $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
    $data_inicio = $_POST['data_inicio'];

    $stmt = $pdo->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, plano=?, valor=?, data_inicio=? WHERE id=?");
    $stmt->execute([$nome, $email, $telefone, $plano, $valor, $data_inicio, $id]);

    header('Location: index.php?msg=Cliente atualizado!');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Editar Cliente: <?= htmlspecialchars($cliente['nome']) ?></h1>
        <a href="index.php" class="btn secondary">Voltar</a>
        <form method="POST">
            <label>Nome *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>">

            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">

            <label>Plano *</label>
            <select name="plano" required>
                <option value="mensal" <?= $cliente['plano']=='mensal'?'selected':'' ?>>Mensal</option>
                <option value="trimestral" <?= $cliente['plano']=='trimestral'?'selected':'' ?>>Trimestral</option>
                <option value="semestral" <?= $cliente['plano']=='semestral'?'selected':'' ?>>Semestral</option>
                <option value="anual" <?= $cliente['plano']=='anual'?'selected':'' ?>>Anual</option>
            </select>

            <label>Valor (R$) *</label>
            <input type="text" name="valor" value="<?= number_format($cliente['valor'], 2, ',', '.') ?>" required>

            <label>Data de Início *</label>
            <input type="date" name="data_inicio" value="<?= $cliente['data_inicio'] ?>" required>

            <button type="submit">Atualizar</button>
        </form>
    </div>
</body>
</html>