<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetchColumn();
if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Registrar pagamento
if ($_POST) {
    $data = $_POST['data_pagamento'];
    $valor = str_replace(['.', ','], ['', '.'], $_POST['valor_pago']);
    $obs = $_POST['observacoes'];
    $stmt = $pdo->prepare("INSERT INTO historico_pagamentos (cliente_id, data_pagamento, valor_pago, observacoes) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $data, $valor, $obs]);
    $msg = "Pagamento registrado!";
}

// Listar histórico
$stmt = $pdo->prepare("SELECT * FROM historico_pagamentos WHERE cliente_id = ? ORDER BY data_pagamento DESC");
$stmt->execute([$id]);
$historico = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - <?= htmlspecialchars($cliente) ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
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
            <h1>Histórico de <?= htmlspecialchars($cliente) ?></h1>
            <a href="index.php" class="btn-back">Voltar</a>
        </header>

        <?php if (isset($msg)): ?>
            <div class="success-box"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST" class="payment-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Valor Pago</label>
                    <input type="text" name="valor_pago" placeholder="99,90" required>
                </div>
                <div class="form-group">
                    <label>Obs</label>
                    <textarea name="observacoes" placeholder="Pix, cartão..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn">Registrar</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Obs</th>
                        <th>QR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $p): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></td>
                        <td>R$ <?= number_format($p['valor_pago'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['observacoes']) ?></td>
                        <td class="qr-cell">
                            <a href="pagar.php?id=<?= $id ?>&valor=<?= $p['valor_pago'] ?>" class="btn-icon">
                                <i class="material-icons">qr_code</i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($historico)): ?>
                    <tr><td colspan="4" class="empty-row">Nenhum pagamento registrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>