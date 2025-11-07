<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
$id = intval($_GET['id'] ?? 0);
$valor_historico = floatval($_GET['valor'] ?? 0);

$stmt = $pdo->prepare("SELECT nome, valor AS valor_plano, plano FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cliente) die('Cliente não encontrado.');

// Usa valor do histórico se existir
$valor_final = $valor_historico > 0 ? $valor_historico : $cliente['valor_plano'];

// === CONFIGURAÇÃO PIX ===
$chave_pix = "3d20dd70-8d51-4e4d-8edb-ce1b383a3fae";
$valor = number_format($valor_final, 2, '.', '');
$nome = substr($cliente['nome'], 0, 25);
$txid = "TX" . time();
$cidade = "SuaCidade";

$payload = "00020126580014BR.GOV.BCB.PIX01" . sprintf("%02d", strlen($chave_pix)) . $chave_pix .
           "520400005303986540" . sprintf("%02d", strlen($valor)) . $valor .
           "5802BR5913$nome6009$cidade62070503$txid6304";
$payload .= strtoupper(dechex(crc32($payload . "6304")));

require_once 'lib/qrlib.php';
ob_start();
QRcode::png($payload, null, QR_ECLEVEL_L, 8, 2);
$qr_image = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar - <?= htmlspecialchars($cliente['nome']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
>

    <div class="main" style="display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;">
        <div class="payment-card">
            <h1>Pagamento</h1>
            <div class="info">
                <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?></p>
                <p><strong>Valor:</strong> R$ <?= number_format($valor_final, 2, ',', '.') ?></p>
                <p><strong>Plano:</strong> <?= ucfirst($cliente['plano']) ?></p>
            </div>
            <div class="qr-box">
                <img src="data:image/png;base64,<?= base64_encode($qr_image) ?>" alt="QR Code Pix">
            </div>
            <p class="copy-label">Ou copie o código Pix:</p>
            <div class="pix-code" id="pixCode"><?= $payload ?></div>
            <button class="btn btn-copy" onclick="copyPix()">Copiar Código</button>
            <div id="msg" class="success">Copiado!</div>
            <button class="btn btn-close" onclick="history.back()">Voltar</button>
        </div>
    </div>

    <script>
    function copyPix() {
        const code = document.getElementById('pixCode').innerText;
        navigator.clipboard.writeText(code).then(() => {
            const msg = document.getElementById('msg');
            msg.style.display = 'block';
            setTimeout(() => msg.style.display = 'none', 2000);
        });
    }
    </script>
</body>
</html>