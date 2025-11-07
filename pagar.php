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
    $map = ['mensal'=>'+1 month','trimestral'=>'+3 months','semestral'=>'+6 months','anual'=>'+1 year'];
    $d->modify($map[strtolower($plano)] ?? '+1 month');
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
       "Após o pagamento, envie-nos o comprovante para renovação.\nObrigado(a)!";

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
    <style>
        /* NEON CARD – FORÇADO NO PAGAR.PHP */
        .payment-card {
            background: rgba(15,25,50,.95);
            padding: 30px;
            border-radius: 25px;
            max-width: 420px;
            width: 100%;
            margin: 20px auto;
            text-align: center;
            box-shadow: 0 0 40px rgba(0,255,255,.6), inset 0 0 20px rgba(0,255,255,.1);
            border: 2px solid #00ffff;
            font-family: 'Roboto',sans-serif;
            color: #e0f7ff;
            position: relative;
            overflow: hidden;
        }
        .payment-card::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(45deg, #00ffff, #00d4ff, #0072ff, #00ffff);
            z-index: -1;
            filter: blur(8px);
            opacity: 0.7;
        }
        .payment-title {
            font-family: 'Orbitron',sans-serif;
            color: #00ffff;
            text-shadow: 0 0 20px #00ffff, 0 0 40px #00ffff;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: glow 2s infinite alternate;
        }
        @keyframes glow {
            from { text-shadow: 0 0 10px #00ffff; }
            to { text-shadow: 0 0 30px #00ffff, 0 0 50px #00ffff; }
        }
        .info { margin:18px 0; line-height:1.8; font-size:1.1rem; }
        .info strong { color:#00d4ff; text-shadow: 0 0 8px #00d4ff; }
        .copy-label { margin:15px 0 8px; font-weight:bold; color:#00d4ff; text-shadow: 0 0 8px #00d4ff; }
        .msg-box {
            background:#001122; padding:16px; border-radius:15px;
            font-family:monospace; font-size:.85rem; color:#00ffea;
            word-break:break-all; border:1px solid #00ffff; margin:15px 0;
            white-space:pre-line; max-height:180px; overflow-y:auto;
            box-shadow: 0 0 15px rgba(0,255,255,.3);
        }
        .btn {
            display:block; width:88%; margin:12px auto; padding:14px;
            border-radius:50px; font-weight:bold; font-size:1.1rem;
            cursor:pointer; transition:.3s; border:none; text-decoration:none;
            text-shadow: 0 0 8px rgba(0,0,0,.5);
        }
        .btn-copy { 
            background: linear-gradient(45deg, #00ff88, #00cc66); 
            color: #000; 
            box-shadow: 0 0 20px rgba(0,255,136,.5);
        }
        .btn-whatsapp { 
            background: linear-gradient(45deg, #25D366, #128C7E); 
            color: #fff; 
            box-shadow: 0 0 20px rgba(37,211,102,.5);
        }
        .btn-close { 
            background: linear-gradient(45deg, #00c6ff, #0072ff); 
            color: #fff; 
            box-shadow: 0 0 20px rgba(0,198,255,.5);
        }
        .btn:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 30px rgba(0,255,255,.7) !important; 
        }
        .success { 
            background: rgba(0,255,136,.2); 
            color: #00ff88; 
            padding: 10px; 
            border-radius: 12px; 
            margin: 10px auto; 
            width: 88%; 
            font-weight: bold;
            border: 1px solid #00ff88;
            box-shadow: 0 0 15px rgba(0,255,136,.4);
        }

        /* RESPONSIVO */
        @media (max-width: 768px) {
            .payment-card { max-width:95%; padding:22px; margin:15px; }
            .msg-box { font-size:.8rem; max-height:160px; }
            .btn { width:92%; font-size:1rem; }
        }
    </style>
</head>
<body>

<!-- HAMBURGUER -->
<div class="hamburger" onclick="toggleSidebar()">
    <span></span><span></span><span></span>
</div>

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
        <!--<h1>Enviar Lembrete</h1>
         <-- <a href="index.php" class="btn-back">Voltar</a> */ -->
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
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}
</script>
    <script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}
</script>
</body>
</html>