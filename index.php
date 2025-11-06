<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
?>
<?php
$stmt = $pdo->query("SELECT * FROM clientes ORDER BY data_inicio DESC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Opcional: Enviar lembretes ao carregar a página (descomente se quiser)
// include 'emails.php';
// sendReminders($pdo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Assinaturas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Clientes e Assinaturas</h1>
            <div>
                <a href="add.php" class="btn">+ Novo Cliente</a>
                <a href="export.php" class="btn">Exportar Excel</a>
                <a href="emails.php" class="btn">Enviar Lembretes</a>
                <a href="logout.php" class="btn danger">Sair</a>
            </div>
        </header>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Plano</th>
                    <th>Valor</th>
                    <th>Início</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>Histórico</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <?php $info = statusCliente($c['data_inicio'], $c['plano']); ?>
                    <tr class="<?= $info['classe'] ?>">
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= ucfirst($c['plano']) ?></td>
                        <td>R$ <?= number_format($c['valor'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($c['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($info['vencimento'])) ?></td>
                        <td><span class="status <?= $info['classe'] ?>"><?= $info['status'] ?></span></td>
                        <td><a href="view_history.php?id=<?= $c['id'] ?>" class="btn small">Ver Pagamentos</a></td>
                        <td>
                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn small">Editar</a>
                            <a href="delete.php?id=<?= $c['id'] ?>" class="btn small danger" onclick="return confirm('Excluir este cliente?')">Excluir</a>
                            <a href="history.php?id=<?= $c['id'] ?>" class="btn small secondary">+ Pagamento</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="8">Nenhum cliente cadastrado. <a href="add.php">Adicione o primeiro!</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>