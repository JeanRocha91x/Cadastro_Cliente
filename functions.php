<?php
function mesesDoPlano(string $plano): int {
    return match($plano) {
        'mensal'      => 1,
        'trimestral'  => 3,
        'semestral'   => 6,
        'anual'       => 12,
        default       => 0,
    };
}

function vencimento(string $dataInicio, string $plano): string {
    $meses = mesesDoPlano($plano);
    return date('Y-m-d', strtotime("$dataInicio + $meses months"));
}

function statusCliente(string $dataInicio, string $plano): array {
    $vcto = vencimento($dataInicio, $plano);
    $hoje = date('Y-m-d');
    $dias = (strtotime($vcto) - strtotime($hoje)) / 86400;

    if ($vcto < $hoje) {
        $status = 'Vencido';
        $classe = 'vencido';
    } elseif ($vcto == $hoje) {
        $status = 'Vence hoje';
        $classe = 'hoje';
    } elseif ($dias <= 7) {
        $status = "Vence em breve ($dias dias)";
        $classe = 'alerta';
    } else {
        $status = 'Ativo';
        $classe = 'ativo';
    }

    return ['status' => $status, 'classe' => $classe, 'vencimento' => $vcto, 'dias' => $dias];
}
?>