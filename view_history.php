<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM historico_pagamentos WHERE cliente_id = ? ORDER BY data_pagamento DESC");
$stmt->execute([$id]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de <?= htmlspecialchars($cliente['nome']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Histórico de Pagamentos - <?= htmlspecialchars($cliente['nome']) ?></h1>
        <a href="index.php" class="btn secondary">Voltar</a>
        <a href="history.php?id=<?= $id ?>" class="btn">+ Novo Pagamento</a>

        <?php if (empty($historico)): ?>
            <p>Nenhum pagamento registrado ainda.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Data</th><th>Valor Pago</th><th>Observações</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $h): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($h['data_pagamento'])) ?></td>
                            <td>R$ <?= number_format($h['valor_pago'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($h['observacoes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>