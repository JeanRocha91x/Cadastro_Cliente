<?php
// qr.php - Apenas redireciona para a página completa
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    http_response_code(403);
    die('Acesso negado.');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    die('ID inválido.');
}

$stmt = $pdo->prepare("SELECT id FROM clientes WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    die('Cliente não encontrado.');
}

// Redireciona para a página com QR Pix completo
header("Location: pagar.php?id=$id");
exit;
?>