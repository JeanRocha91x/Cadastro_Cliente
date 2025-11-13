<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$search = $_GET['q'] ?? '';
$where  = $search ? "WHERE nome LIKE :search OR telefone LIKE :search" : "";
$sql    = "SELECT * FROM clientes $where ORDER BY data_inicio DESC";
$stmt   = $pdo->prepare($sql);
if ($search) $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$nomes_avencer = [];

foreach ($clientes as $c) {
    $venc = vencimento($c['data_inicio'], $c['plano']);
    $dias = (int) floor((strtotime($venc) - $today)/86400);
    if ($dias >= 0 && $dias <= 3) {
        $nomes_avencer[] = $c['nome'];
    }
}

// Modo polling JSON
if (isset($_GET['poll']) && $_GET['poll'] === '1') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($nomes_avencer);
    exit;
}

// Modo SSE simples (um evento e encerra)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

echo "data: ".json_encode($nomes_avencer)."\n\n";
@ob_flush();
@flush();
exit;
