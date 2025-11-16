<?php
require_once 'config.php';
require_once 'functions.php';

// --- Cabeçalhos de segurança básicos ---
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// ATENÇÃO: o CSP abaixo bloqueia JS inline (inclui onclick do hambúrguer).
// Para InfinityFree + este layout, é melhor deixar comentado.
// header("Content-Security-Policy: default-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' https://cdnjs.cloudflare.com");

requireLogin();

// --- Helper para WhatsApp: aceita BR/PT automaticamente e permite forçar via ?ddi=55/351 ---
if (!function_exists('wa_phone')) {
    function wa_phone($raw, $force_ddi = null) {
        $n = preg_replace('/\D+/', '', (string)$raw);
        if ($n === '') return $n;

        // Se já vier com DDI BR/PT, mantém
        if (preg_match('/^(55|351)/', $n)) {
            return $n;
        }

        // Se vier ?ddi=55 ou ?ddi=351, aplica
        if ($force_ddi === '55' || $force_ddi === '351') {
            return $force_ddi . $n;
        }

        // Auto-detecção:
        // - 11+ dígitos -> BR (55)
        // - 9 dígitos   -> PT (351)
        // - fallback    -> BR (55)
        $len = strlen($n);
        if ($len >= 11) return '55' . $n;
        if ($len == 9)  return '351' . $n;
        return '55' . $n;
    }
}

$force_ddi = null;
if (isset($_GET['ddi']) && preg_match('/^(55|351)$/', $_GET['ddi'])) {
    $force_ddi = $_GET['ddi'];
}

// --- Stats gerais ---
$stats  = getStats($pdo);

// --- Busca ---
$search = $_GET['q'] ?? '';
$where  = $search ? "WHERE nome LIKE :search OR telefone LIKE :search" : "";

// --- Paginação ---
$per  = max(5, min(100, (int)($_GET['per'] ?? 20)));
$page = max(1, (int)($_GET['page'] ?? 1));
$off  = ($page - 1) * $per;

// Conta total de registros pra paginar
$sqlCount = "SELECT COUNT(*) FROM clientes $where";
$stmtCount = $pdo->prepare($sqlCount);
if ($search) {
    $stmtCount->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmtCount->execute();
$total_rows  = (int) $stmtCount->fetchColumn();
$total_pages = max(1, (int) ceil($total_rows / $per));

// Busca os clientes da página atual
$sqlList = "SELECT * FROM clientes $where ORDER BY data_inicio DESC LIMIT :off, :per";
$stmt = $pdo->prepare($sqlList);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':off', $off, PDO::PARAM_INT);
$stmt->bindValue(':per', $per, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Filtro pelos cards: ativos | avencer | vencidos | todos ---
$f = $_GET['f'] ?? 'todos';
$f = preg_replace('/[^a-z]/', '', strtolower($f));

// Contadores e filtragem baseados no próximo vencimento (na página atual)
$counts = ['ativos' => 0, 'avencer' => 0, 'vencidos' => 0, 'total' => count($clientes)];
$today  = strtotime(date('Y-m-d'));

$clientes_filtrados = [];
foreach ($clientes as $cli) {
    $venc = strtotime(vencimento($cli['data_inicio'], $cli['plano']));
    $dias = (int) floor(($venc - $today) / 86400);

    if ($dias < 0) {
        $bucket = 'vencidos';
    } elseif ($dias <= 3) {
        $bucket = 'avencer';
    } else {
        $bucket = 'ativos';
    }

    $counts[$bucket]++;

    if ($f === 'todos' || $f === $bucket) {
        $clientes_filtrados[] = $cli;
    }
}

// Se for "todos", mantém a lista original da página
if ($f === 'todos') {
    $clientes_filtrados = $clientes;
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<!-- HAMBURGUER (sempre no topo) -->
<div class="hamburger" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
</div>

<div class="sidebar">
    <div class="logo">Sistema X</div>
    <a href="index.php" class="active"><i class="material-icons">dashboard</i> Dashboard</a>
    <a href="add.php"><i class="material-icons">person_add</i> Novo Cliente</a>
    <a href="relatorio.php"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
    <a href="export.php"><i class="material-icons">download</i> Exportar</a>
    <a href="backup.php"><i class="material-icons">archive</i> Backup</a>
    <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
</div>

<div class="main">
    <header>
        <h1>Dashboard</h1>
        <form class="search-form" method="get" action="index.php">
            <input type="hidden" name="f"   value="<?= htmlspecialchars($f) ?>">
            <input type="hidden" name="per" value="<?= (int)$per ?>">
            <?php if ($force_ddi): ?>
                <input type="hidden" name="ddi" value="<?= htmlspecialchars($force_ddi) ?>">
            <?php endif; ?>
            <input type="text" name="q" placeholder="Pesquisar por nome ou telefone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="material-icons">search</i></button>
        </form>
    </header>

    <!-- CARDS -->
    <div class="stats-grid">
        <a class="stat-card-link" href="index.php?f=todos&q=<?= urlencode($search) ?>&page=1&per=<?= (int)$per ?><?= $force_ddi ? '&ddi='.$force_ddi : '' ?>">
            <div class="stat-card total">
                <i class="material-icons">people</i>
                <div>
                    <h3><?= $stats['total'] ?></h3>
                    <p>Total de Clientes</p>
                </div>
            </div>
        </a>

        <a class="stat-card-link" href="index.php?f=ativos&q=<?= urlencode($search) ?>&page=<?= (int)$page ?>&per=<?= (int)$per ?><?= $force_ddi ? '&ddi='.$force_ddi : '' ?>">
            <div class="stat-card ativo">
                <i class="material-icons">check_circle</i>
                <div>
                    <h3><?= $stats['ativos'] ?></h3>
                    <p>Clientes Ativos</p>
                </div>
            </div>
        </a>

        <a class="stat-card-link" href="index.php?f=avencer&q=<?= urlencode($search) ?>&page=<?= (int)$page ?>&per=<?= (int)$per ?><?= $force_ddi ? '&ddi='.$force_ddi : '' ?>">
            <div class="stat-card avencer">
                <i class="material-icons">schedule</i>
                <div>
                    <h3><?= $counts['avencer'] ?></h3>
                    <p>Clientes a Vencer</p>
                </div>
            </div>
        </a>

        <a class="stat-card-link" href="index.php?f=vencidos&q=<?= urlencode($search) ?>&page=<?= (int)$page ?>&per=<?= (int)$per ?><?= $force_ddi ? '&ddi='.$force_ddi : '' ?>">
            <div class="stat-card vencido">
                <i class="material-icons">error</i>
                <div>
                    <h3><?= $stats['vencidos'] ?></h3>
                    <p>Clientes Vencidos</p>
                </div>
            </div>
        </a>

        <div class="stat-card planos">
            <i class="material-icons">pie_chart</i>
            <div>
                <h3><?= count($stats['planos']) ?></h3>
                <p>Tipos de Planos</p>
            </div>
        </div>
    </div>

    <!-- TABELA -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Plano</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>WA</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes_filtrados as $c): $info = statusCliente($c['data_inicio'], $c['plano']); ?>
                <tr class="<?= $info['classe'] ?>">
                    <td><?= htmlspecialchars($c['nome']) ?></td>
                    <td><?= htmlspecialchars($c['telefone'] ?? '—') ?></td>
                    <td><?= ucfirst($c['plano']) ?></td>
                    <td><?= date('d/m/Y', strtotime(vencimento($c['data_inicio'], $c['plano']))) ?></td>
                    <td><span class="status <?= $info['classe'] ?>"><?= $info['status'] ?></span></td>
                    <td class="wa-cell">
                        <?php
                            // Se quiser manter a página pagar.php, deixe como está:
                            // $waHref = 'pagar.php?id='.$c['id'];
                            // Se quiser link direto pro WhatsApp, troque para:
                            // $waHref = 'https://wa.me/'.wa_phone($c['telefone'], $force_ddi).'?text='.urlencode('Olá '.$c['nome'].', tudo bem?');
                            $waHref = 'pagar.php?id='.$c['id'];
                        ?>
                        <a href="<?= htmlspecialchars($waHref) ?>" class="btn-icon">
                            <i class="fa-brands fa-square-whatsapp"></i>
                        </a>
                    </td>
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

    <!-- Paginação -->
    <div class="pagination" style="margin-top:16px; display:flex; gap:10px; align-items:center;">
        <?php
        $baseParams = 'per='.$per.'&q='.urlencode($search).'&f='.$f.($force_ddi ? '&ddi='.$force_ddi : '');
        ?>
        <?php if ($page > 1): ?>
            <a class="btn-page" href="index.php?page=<?= $page-1 ?>&<?= $baseParams ?>">&laquo; Anterior</a>
        <?php else: ?>
            <span class="btn-page disabled">&laquo; Anterior</span>
        <?php endif; ?>

        <span class="page-indicator">Página <?= (int)$page ?> de <?= (int)$total_pages ?></span>

        <?php if ($page < $total_pages): ?>
            <a class="btn-page" href="index.php?page=<?= $page+1 ?>&<?= $baseParams ?>">Próxima &raquo;</a>
        <?php else: ?>
            <span class="btn-page disabled">Próxima &raquo;</span>
        <?php endif; ?>
    </div>
</div>

<script>
if (Notification.permission === 'default') Notification.requestPermission();

// SSE + fallback para polling (InfinityFree pode matar SSE)
let esOk = true;
try {
    const es = new EventSource('notifications.php');
    es.onerror = () => { esOk = false; es.close(); };
    es.onmessage = e => {
        try {
            const nomes = JSON.parse(e.data);
            (nomes || []).forEach(n =>
                new Notification('Lembrete', {
                    body: `${n} vence em 3 dias!`,
                    icon: 'icons/icon-192.png'
                })
            );
        } catch (_) {}
    };
    setTimeout(() => { if (!esOk) startPolling(); }, 5000);
} catch(e) {
    esOk = false;
    startPolling();
}

function startPolling(){
    setInterval(async () => {
        try {
            const r = await fetch('notifications.php?poll=1', { cache:'no-store' });
            if (!r.ok) return;
            const nomes = await r.json();
            (nomes || []).forEach(n =>
                new Notification('Lembrete', {
                    body: `${n} vence em 3 dias!`,
                    icon: 'icons/icon-192.png'
                })
            );
        } catch(e){}
    }, 60000); // 1 min
}

// ABRIR / FECHAR MENU LATERAL (MOBILE)
function toggleSidebar() {
    const sidebar  = document.querySelector('.sidebar');
    const burger   = document.querySelector('.hamburger');

    if (sidebar) sidebar.classList.toggle('open');
    if (burger)  burger.classList.toggle('open');
}
</script>
</body>
</html>
