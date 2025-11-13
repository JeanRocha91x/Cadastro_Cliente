<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function csv_emit($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo "\xEF\xBB\xBF"; // BOM
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $headers);
    foreach ($rows as $r) fputcsv($fp, $r);
    fclose($fp);
    exit;
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$hist     = $pdo->query("SELECT * FROM historico_pagamentos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$action = $_GET['action'] ?? '';

if ($action === 'clientes') {
    $rows = [];
    foreach ($clientes as $c) {
        $rows[] = [$c['id'],$c['nome'],$c['telefone'],$c['plano'],$c['valor'],$c['data_inicio']];
    }
    csv_emit('clientes_'.date('Ymd_His').'.csv', ['ID','Nome','Telefone','Plano','Valor','Data Início'], $rows);
}

if ($action === 'historico') {
    $rows = [];
    foreach ($hist as $h) {
        $rows[] = [$h['id'],$h['cliente_id'],$h['valor_pago'],$h['data_pagamento']];
    }
    csv_emit('historico_'.date('Ymd_His').'.csv', ['ID','Cliente ID','Valor Pago','Data Pagamento'], $rows);
}

if ($action === 'zip') {
    if (class_exists('ZipArchive')) {
        $zipname = 'backup_'.date('Ymd_His').'.zip';
        $tmp = tempnam(sys_get_temp_dir(), 'bk');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) === true) {
            $csv1 = "ID,Nome,Telefone,Plano,Valor,Data Início\n";
            foreach ($clientes as $c) {
                $csv1 .= sprintf("%s,%s,%s,%s,%s,%s\n",
                    $c['id'],
                    '"'.str_replace('"','""',$c['nome']).'"',
                    '"'.str_replace('"','""',$c['telefone']).'"',
                    '"'.str_replace('"','""',$c['plano']).'"',
                    $c['valor'],
                    $c['data_inicio']
                );
            }
            $csv2 = "ID,Cliente ID,Valor Pago,Data Pagamento\n";
            foreach ($hist as $h) {
                $csv2 .= sprintf("%s,%s,%s,%s\n",
                    $h['id'],
                    $h['cliente_id'],
                    $h['valor_pago'],
                    $h['data_pagamento']
                );
            }
            $zip->addFromString('clientes.csv', $csv1);
            $zip->addFromString('historico_pagamentos.csv', $csv2);
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.$zipname.'"');
            header('Content-Length: '.filesize($tmp));
            readfile($tmp);
            @unlink($tmp);
            exit;
        }
    }
    // Fallback caso não tenha ZipArchive
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h3>ZipArchive indisponível — baixe os CSVs individualmente:</h3>';
    echo '<ul>';
    echo '<li><a href="?action=clientes">Baixar clientes.csv</a></li>';
    echo '<li><a href="?action=historico">Baixar historico_pagamentos.csv</a></li>';
    echo '</ul>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Backup</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:720px;margin:32px auto;">
    <h2 style="margin-bottom:16px;">Backup de Dados</h2>
    <p>Faça download dos CSVs individuais ou gere um ZIP com tudo.</p>
    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:16px;">
        <a class="btn-page" href="?action=clientes">Baixar clientes.csv</a>
        <a class="btn-page" href="?action=historico">Baixar historico_pagamentos.csv</a>
        <a class="btn-page" href="?action=zip">Baixar ZIP completo</a>
    </div>
</div>
</body>
</html>
