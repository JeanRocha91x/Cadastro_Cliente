<?php
function mesesDoPlano($plano) {
    switch ($plano) {
        case 'mensal': return 1;
        case 'trimestral': return 3;
        case 'semestral': return 6;
        case 'anual': return 12;
        default: return 0;
    }
}

function vencimento($dataInicio, $plano) {
    $meses = mesesDoPlano($plano);
    return date('Y-m-d', strtotime("$dataInicio + $meses months"));
}

function statusCliente($dataInicio, $plano) {
    $vcto = vencimento($dataInicio, $plano);
    $hoje = date('Y-m-d');
    $dias = (strtotime($vcto) - strtotime($hoje)) / 86400;

    if ($vcto < $hoje) return ['status' => 'Vencido', 'classe' => 'vencido'];
    elseif ($vcto == $hoje) return ['status' => 'Vence hoje', 'classe' => 'hoje'];
    elseif ($dias <= 7) return ['status' => "Vence em " . round($dias) . " dias", 'classe' => 'alerta'];
    else return ['status' => 'Ativo', 'classe' => 'ativo'];
}

function getNotifications($pdo) {
    $sql = "SELECT nome FROM clientes WHERE DATE_ADD(data_inicio, INTERVAL CASE plano WHEN 'mensal' THEN 1 WHEN 'trimestral' THEN 3 WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 END MONTH) = DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
}
?>