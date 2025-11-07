<?php
session_start();
require_once 'config.php';
requireLogin();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$notifs = getNotifications($pdo);
echo "data: " . json_encode($notifs) . "\n\n";
flush();
?>