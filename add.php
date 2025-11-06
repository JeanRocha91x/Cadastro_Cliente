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

    header('Location: index.php?msg=Cliente cadastrado com sucesso!');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Cliente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Novo Cliente</h1>
        <a href="index.php" class="btn secondary">Voltar</a>
        <form method="POST">
            <label>Nome *</label>
            <input type="text" name="nome" required>

            <label>Email</label>
            <input type="email" name="email">

            <label>Telefone</label>
            <input type="text" name="telefone" placeholder="(00) 00000-0000">

            <label>Plano *</label>
            <select name="plano" required>
                <option value="mensal">Mensal</option>
                <option value="trimestral">Trimestral</option>
                <option value="semestral">Semestral</option>
                <option value="anual">Anual</option>
            </select>

            <label>Valor (R$) *</label>
            <input type="text" name="valor" placeholder="99,90" required>

            <label>Data de Início *</label>
            <input type="date" name="data_inicio" value="<?= date('Y-m-d') ?>" required>

            <button type="submit">Salvar Cliente</button>
        </form>
    </div>
</body>
</html>