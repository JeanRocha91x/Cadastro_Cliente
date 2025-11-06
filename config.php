<?php
session_start();

// Configurações do Banco de Dados (SUBSTITUA PELAS SUAS!)
$host = 'XXXXXXXXX';     // Ex: sql123456.epizy.com
$dbname = 'XXXXXXXXXX'; // Nome do seu banco
$username = 'XXXXXXXXXX';      // Usuário do DB
$password = 'XXXXXXXXXXX';   // Senha do DB

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
