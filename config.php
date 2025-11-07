<?php
session_start();

// Configurações do Banco de Dados (SUBSTITUA PELAS SUAS!)
$host = 'sql110.infinityfree.com';     // Ex: sql123456.epizy.com
$dbname = 'if0_40344657_system'; // Nome do seu banco
$username = 'if0_40344657';      // Usuário do DB
$password = 'a1YGck2NUsQblCX';   // Senha do DB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '123456');

function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function getStats($pdo) {
    $total = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $ativos = $pdo->query("SELECT COUNT(*) FROM clientes WHERE DATE_ADD(data_inicio, INTERVAL CASE plano WHEN 'mensal' THEN 1 WHEN 'trimestral' THEN 3 WHEN 'semestral' THEN 6 WHEN 'anual' THEN 12 END MONTH) >= CURDATE()")->fetchColumn();
    $vencidos = $total - $ativos;
    $planos = $pdo->query("SELECT plano, COUNT(*) as count FROM clientes GROUP BY plano")->fetchAll(PDO::FETCH_KEY_PAIR);
    return ['total' => $total, 'ativos' => $ativos, 'vencidos' => $vencidos, 'planos' => $planos];
}
?>