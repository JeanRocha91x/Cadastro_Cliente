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
    $data_pagamento = $_POST['data_pagamento'];
    $valor_pago = str_replace(['.', ','], ['', '.'], $_POST['valor_pago']);
    $observacoes = $_POST['observacoes'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO historico_pagamentos (cliente_id, data_pagamento, valor_pago, observacoes) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $data_pagamento, $valor_pago, $observacoes]);

    header('Location: index.php?msg=Pagamento registrado!');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Pagamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Adicionar Pagamento para <?= htmlspecialchars($cliente['nome']) ?></h1>
        <a href="index.php" class="btn secondary">Voltar</a>
        <form method="POST">
            <label>Data do Pagamento</label>
            <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>

            <label>Valor Pago (R$)</label>
            <input type="text" name="valor_pago" placeholder="99,90" required>

            <label>Observações</label>
            <textarea name="observacoes" rows="3"></textarea>

            <button type="submit">Registrar Pagamento</button>
        </form>
    </div>
</body>
</html>