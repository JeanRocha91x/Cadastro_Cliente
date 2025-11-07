<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
$stats = getStats($pdo);
$search = $_GET['q'] ?? '';
$where = $search ? "WHERE nome LIKE :search OR telefone LIKE :search" : "";
$stmt = $pdo->prepare("SELECT * FROM clientes $where ORDER BY data_inicio DESC");
$stmt->execute($search ? [':search' => "%$search%"] : []);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="logo">Sistema X</div>
        <a href="index.php" class="active"><i class="material-icons">dashboard</i> Dashboard</a>
        <a href="add.php"><i class="material-icons">person_add</i> Novo Cliente</a>
        <a href="relatorio.php"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
        <a href="export.php"><i class="material-icons">download</i> Exportar</a>
        <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
    </div>
    <div class="main">
        <header>
            <h1>Dashboard</h1>
            <form class="search-form">
                <input type="text" name="q" placeholder="Pesquisar por nome ou telefone..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="material-icons">search</i></button>
            </form>
        </header>
        <div class="stats-grid">
            <div class="stat-card total">
                <i class="material-icons">people</i>
                <div>
                    <h3><?= $stats['total'] ?></h3>
                    <p>Total de Clientes</p>
                </div>
            </div>
            <div class="stat-card ativo">
                <i class="material-icons">check_circle</i>
                <div>
                    <h3><?= $stats['ativos'] ?></h3>
                    <p>Clientes Ativos</p>
                </div>
            </div>
            <div class="stat-card vencido">
                <i class="material-icons">error</i>
                <div>
                    <h3><?= $stats['vencidos'] ?></h3>
                    <p>Clientes Vencidos</p>
                </div>
            </div>
            <div class="stat-card planos">
                <i class="material-icons">pie_chart</i>
                <div>
                    <h3><?= count($stats['planos']) ?></h3>
                    <p>Tipos de Planos</p>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Plano</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>QR</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): $info = statusCliente($c['data_inicio'], $c['plano']); ?>
                    <tr class="<?= $info['classe'] ?>">
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= htmlspecialchars($c['telefone'] ?? '—') ?></td>
                        <td><?= ucfirst($c['plano']) ?></td>
                        <td><?= date('d/m/Y', strtotime(vencimento($c['data_inicio'], $c['plano']))) ?></td>
                        <td><span class="status <?= $info['classe'] ?>"><?= $info['status'] ?></span></td>
                        <td><a href="pagar.php?id=<?= $c['id'] ?>" class="btn-icon"><i class="material-icons">qr_code</i></a></td>
                        <td class="actions">
                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn-icon"><i class="material-icons">edit</i></a>
                            <a href="delete.php?id=<?= $c['id'] ?>" class="btn-icon danger" onclick="return confirm('Excluir?')"><i class="material-icons">delete</i></a>
                            <a href="history.php?id=<?= $c['id'] ?>" class="btn-icon"><i class="material-icons">history</i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        if (Notification.permission === 'default') Notification.requestPermission();
        const es = new EventSource('notifications.php');
        es.onmessage = e => {
            const nomes = JSON.parse(e.data);
            nomes.forEach(n => new Notification('Lembrete', { body: `${n} vence em 3 dias!`, icon: 'icons/icon-192.png' }));
        };
    </script>
</body>
</html>