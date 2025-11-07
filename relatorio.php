<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$mes = $_GET['mes'] ?? date('Y-m');
$inicio = "$mes-01";
$fim = date('Y-m-t', strtotime($inicio));

$stmt = $pdo->prepare("
    SELECT c.nome, c.valor AS valor_plano, h.*
    FROM historico_pagamentos h
    JOIN clientes c ON h.cliente_id = c.id
    WHERE h.data_pagamento BETWEEN ? AND ?
    ORDER BY h.data_pagamento DESC
");
$stmt->execute([$inicio, $fim]);
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = $pdo->prepare("SELECT SUM(valor_pago) as total FROM historico_pagamentos WHERE data_pagamento BETWEEN ? AND ?");
$total->execute([$inicio, $fim]);
$total_mes = $total->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Mensal</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="logo">Sistema X</div>
        <a href="index.php"><i class="material-icons">dashboard</i> Dashboard</a>
        <a href="add.php"><i class="material-icons">person_add</i> Novo Cliente</a>
        <a href="relatorio.php" class="active"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
        <a href="export.php"><i class="material-icons">download</i> Exportar</a>
        <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
    </div>

    <div class="main">
        <header>
            <h1>Relatório Mensal</h1>
            <form class="search-form" style="gap:10px;">
                <input type="month" name="mes" value="<?= $mes ?>" onchange="this.form.submit()">
                <a href="index.php" class="btn-back">Voltar</a>
            </form>
        </header>

        <div class="total-box">
            Total Recebido: R$ <?= number_format($total_mes, 2, ',', '.') ?>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Plano</th>
                        <th>Obs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagamentos as $p): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td>R$ <?= number_format($p['valor_pago'], 2, ',', '.') ?></td>
                        <td><?= ucfirst($p['plano'] ?? 'Mensal') ?></td>
                        <td><?= htmlspecialchars($p['observacoes']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pagamentos)): ?>
                    <tr><td colspan="5" class="empty-row">Nenhum pagamento registrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>