<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

function sendReminders($pdo) {
    // Query para clientes que vencem em 3 dias
    $clientes = $pdo->query("
        SELECT * FROM clientes 
        WHERE DATE_ADD(data_inicio, INTERVAL 
            CASE plano
                WHEN 'mensal' THEN 1
                WHEN 'trimestral' THEN 3
                WHEN 'semestral' THEN 6
                WHEN 'anual' THEN 12
            END MONTH
        ) = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        AND email IS NOT NULL
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $enviados = 0;
    foreach ($clientes as $c) {
        $info = statusCliente($c['data_inicio'], $c['plano']);
        $assunto = "Lembrete: Assinatura vence em 3 dias!";
        $mensagem = "Olá {$c['nome']},\n\nSua assinatura vence em " . date('d/m/Y', strtotime($info['vencimento'])) . ".\n\nPague para evitar interrupções.\n\nAtenciosamente,\nSua Empresa";
        $headers = "From: no-reply@seudominio.epizy.com\r\n";
        
        if (mail($c['email'], $assunto, $mensagem, $headers)) {
            $enviados++;
        }
    }
    return $enviados;
}

if (isset($_GET['send'])) {
    $enviados = sendReminders($pdo);
    header("Location: index.php?msg={$enviados} lembretes enviados!");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Enviar Lembretes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Enviar Lembretes Automáticos</h1>
        <p>Envie emails para clientes que vencem em 3 dias.</p>
        <a href="?send=1" class="btn">Enviar Agora</a>
        <a href="index.php" class="btn secondary">Voltar</a>
    </div>
</body>
</html>