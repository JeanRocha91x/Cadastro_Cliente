<?php require_once 'config.php'; ?>
<?php
if ($_POST) {
    if ($_POST['user'] === ADMIN_USER && $_POST['pass'] === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuário ou senha inválidos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Gerenciador de Assinaturas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h1>Login do Admin</h1>
        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Usuário</label>
            <input type="text" name="user" required>
            
            <label>Senha</label>
            <input type="password" name="pass" required>
            
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>