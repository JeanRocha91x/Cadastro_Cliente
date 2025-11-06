<?php
require_once 'config.php';
requireLogin();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?msg=Cliente excluído!');
exit;
?>