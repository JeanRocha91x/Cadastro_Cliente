<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('ID inválido.');

$stmt = $pdo->prepare("SELECT nome, telefone, plano, valor, data_inicio FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cliente) die('Cliente não encontrado.');

function vencimento($data, $plano) {
    $d = new DateTime($data);
    $plano = strtolower($plano);
    $map = ['mensal'=>'+1 month','trimestral'=>'+3 months','semestral'=>'+6 months','anual'=>'+1 year'];
    $d->modify($map[$plano] ?? '+1 month');
    return $d->format('d/m/Y');
}
$venc = vencimento($cliente['data_inicio'], $cliente['plano']);
$tel = preg_replace('/\D/', '', $cliente['telefone']);
$planoUc = ucfirst($cliente['plano']);
$valor = number_format($cliente['valor'],2,',','.');
$chave = "3d20dd70-8d51-4e4d-8edb-ce1b383a3fae";

$msg = "Olá {$cliente['nome']}, sua assinatura ($planoUc) está para vencer em $venc.\n\n".
       "Para renovação, siga a nossa chave Pix:\n$chave\n".
       "Valor: R$ $valor\n\n".
       "Após o pagamento, envie-nos o comprovante para renovação.\nObrigado(s)!";

$wa = "https://wa.me/55$tel?text=".urlencode($msg);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Lembrete – <?=htmlspecialchars($cliente['nome'])?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">Sistema X</div>
    <a href="index.php"><i class="material-icons">dashboard</i> Dashboard</a>
    <a href="add.php"><i class="material-icons">person_add</i> Novo Cliente</a>
    <a href="relatorio.php"><i class="material-icons">bar_chart</i> Relatório Mensal</a>
    <a href="export.php"><i class="material-icons">download</i> Exportar</a>
    <a href="logout.php"><i class="material-icons">logout</i> Sair</a>
</div>

<div class="main">
    <header>
        <h1>Enviar Lembrete</h1>
        <a href="index.php" class="btn-back">Voltar</a>
    </header>

    <div class="payment-card">
        <h2 class="payment-title">Lembrete de Renovação</h2>
        <div class="info">
            <p><strong>Cliente:</strong> <?=htmlspecialchars($cliente['nome'])?></p>
            <p><strong>Telefone:</strong> <?=htmlspecialchars($cliente['telefone'])?></p>
            <p><strong>Plano:</strong> <?=$planoUc?></p>
            <p><strong>Vencimento:</strong> <?=$venc?></p>
            <p><strong>Valor:</strong> R$ <?=$valor?></p>
        </div>

        <p class="copy-label">Mensagem para WhatsApp:</p>
        <pre id="mensagem" class="msg-box"><?=htmlspecialchars($msg)?></pre>

        <button class="btn btn-copy" onclick="copyMsg()">Copiar Mensagem</button>
        <div id="copiado" class="success" style="display:none;">Copiado!</div>

        <a href="<?=$wa?>" target="_blank" class="btn btn-whatsapp">Abrir WhatsApp</a>
        <button class="btn btn-close" onclick="history.back()">Voltar</button>
    </div>
</div>

<script>
function copyMsg(){
    const txt = document.getElementById('mensagem').innerText;
    navigator.clipboard.writeText(txt).then(()=>{ 
        const el = document.getElementById('copiado');
        el.style.display='block';
        setTimeout(()=>el.style.display='none',2000);
    });
}
</script>
</body>
</html>