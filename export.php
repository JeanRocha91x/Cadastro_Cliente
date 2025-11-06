<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="clientes.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nome', 'Email', 'Plano', 'Valor', 'Início', 'Status']);

foreach ($clientes as $c) {
    $info = statusCliente($c['data_inicio'], $c['plano']);
    fputcsv($output, [
        $c['nome'], $c['email'], $c['plano'], $c['valor'], 
        $c['data_inicio'], $info['status']
    ]);
}

fclose($output);
exit;
?>