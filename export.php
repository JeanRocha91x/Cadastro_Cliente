<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function csv_safe($v){
    $v = (string)$v;
    return preg_match('/^[=+\-@]/', $v) ? "'".$v : $v;
}

$search = $_GET['q'] ?? '';
$f      = isset($_GET['f']) ? preg_replace('/[^a-z]/','', strtolower($_GET['f'])) : 'todos';

$where  = $search ? "WHERE nome LIKE :search OR telefone LIKE :search" : "";
$sql    = "SELECT * FROM clientes $where ORDER BY data_inicio DESC";
$stmt   = $pdo->prepare($sql);
if ($search) $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$out = [];

foreach ($clientes as $c) {
    $venc     = vencimento($c['data_inicio'], $c['plano']);
    $venc_ts  = strtotime($venc);
    $dias     = (int) floor(($venc_ts - $today)/86400);
    $status   = statusCliente($c['data_inicio'], $c['plano']);
    $bucket   = ($dias < 0) ? 'vencidos' : (($dias <= 3) ? 'avencer' : 'ativos');

    if ($f === 'todos' || $f === $bucket) {
        $out[] = [
            'id'            => $c['id'],
            'nome'          => $c['nome'],
            'telefone'      => $c['telefone'],
            'plano'         => $c['plano'],
            'data_inicio'   => $c['data_inicio'],
            'proximo_venc'  => $venc,
            'status'        => $status['status']
        ];
    }
}

$filename = 'clientes_export_'.date('Ymd_His').'.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

echo "\xEF\xBB\xBF"; // BOM UTF-8
$fp = fopen('php://output', 'w');
fputcsv($fp, ['ID','Nome','Telefone','Plano','Data Início','Próximo Vencimento','Status']);

foreach ($out as $row) {
    fputcsv($fp, [
        csv_safe($row['id']),
        csv_safe($row['nome']),
        csv_safe($row['telefone']),
        csv_safe($row['plano']),
        csv_safe(date('d/m/Y', strtotime($row['data_inicio']))),
        csv_safe(date('d/m/Y', strtotime($row['proximo_venc']))),
        csv_safe($row['status'])
    ]);
}
fclose($fp);
exit;
