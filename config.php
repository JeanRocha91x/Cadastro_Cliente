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

// Credenciais de Admin (mude se quiser!)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '123456');

function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>