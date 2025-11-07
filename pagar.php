<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID inválido.');
}

try {
    $stmt = $pdo->prepare("SELECT nome, telefone, plano, valor, data_inicio FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        die('Cliente não encontrado.');
    }
} catch (PDOException $e) {
    die('Erro no banco: ' . $e->getMessage());
}

// === FUNÇÃO VENCIMENTO (de functions.php) ===
if (!function_exists('vencimento')) {
    function vencimento($data_inicio, $plano) {
        $inicio = new DateTime($data_inicio);
        switch ($plano) {
            case 'mensal': $inicio->modify('+1 month'); break;
            case 'trimestral': $inicio->modify('+3 months'); break;
            case 'semestral': $inicio->modify('+6 months'); break;
            case 'anual': $inicio->modify('+1 year'); break;
            default: $inicio->modify('+1 month');
        }
        return $inicio->format('Y-m-d');
    }
}

$vencimento = date('d/m/Y', strtotime(vencimento($cliente['data_inicio'], $cliente['plano'])));

// === LIMPA TELEFONE ===
$telefone = preg_replace('/\D/', '', $cliente['telefone']);
if (strlen($telefone) < 10 || strlen($telefone) > 11) {
    die('Telefone inválido. Use (DD) 9XXXX-XXXX.');
}

// === MENSAGEM PERSONALIZADA ===
$plano_uc = ucfirst($cliente['plano']);
$valor = number_format($cliente['valor'], 2, ',', '.');
$chave_pix = "3d20dd70-8d51-4e4d-8edb-ce1b383a3fae";

$mensagem = "Olá {$cliente['nome']}, sua assinatura ($plano_uc) está para vencer em $vencimento.\n\n" .
            "Para renovação, siga a nossa chave Pix:\n$chave_pix\n" .
            "Valor: R$ $valor\n\n" .
            "Após o pagamento, envie-nos o comprovante para renovação.\nObrigado(s)!";

$whatsapp_url = "https://wa.me/55$telefone?text=" . urlencode($mensagem);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - <?= htmlspecialchars($cliente['nome']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="main" style="display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;">
        <div class="payment-card">
            <h1>Enviar Lembrete</h1>
            <div class="info">
                <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($cliente['telefone']) ?></p>
                <p><strong>Plano:</strong> <?= $plano_uc ?></p>
                <p><strong>Vencimento:</strong> <?= $vencimento ?></p>
                <p><strong>Valor:</strong> R$ <?= $valor ?></p>
            </div>

            <p class="copy-label">Mensagem para WhatsApp:</p>
            <div class="pix-code" id="mensagem"><?= nl2br(htmlspecialchars($mensagem)) ?></div>

            <button class="btn btn-copy" onclick="copyMsg()">Copiar Mensagem</button>
            <div id="msg" class="success">Copiado!</div>

            <a href="<?= $whatsapp_url ?>" target="_blank" class="btn btn-whatsapp">
                Abrir WhatsApp
            </a>

            <button class="btn btn-close" onclick="history.back()">Voltar</button>
        </div>
    </div>

    <script>
    function copyMsg() {
        const msg = document.getElementById('mensagem').innerText;
        navigator.clipboard.writeText(msg).then(() => {
            const el = document.getElementById('msg');
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 2000);
        });
    }
    </script>
</body>
</html>