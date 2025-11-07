<?php
require_once 'config.php';
if ($_POST) {
    if ($_POST['user'] === ADMIN_USER && $_POST['pass'] === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1a2e, #16213e, #0f0f1e);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Orbitron', sans-serif;
        }
        .login-box {
            background: rgba(20, 20, 40, 0.9);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.4);
            text-align: center;
            width: 100%;
            max-width: 380px;
            border: 1px solid rgba(0, 212, 255, 0.3);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .login-box h1 {
            color: #00ffea;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-shadow: 0 0 20px #00d4ff, 0 0 40px #00d4ff;
            letter-spacing: 2px;
        }
        .login-box label {
            display: block;
            text-align: left;
            color: #00d4ff;
            margin: 15px 0 5px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login-box input {
            width: 100%;
            padding: 14px;
            background: rgba(15, 15, 35, 0.8);
            border: 1px solid rgba(0, 212, 255, 0.4);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .login-box input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.6);
        }
        .login-box button {
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0, 198, 255, 0.4);
        }
        .login-box button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 198, 255, 0.6);
        }
        .error {
            color: #ff3b30;
            margin: 10px 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Login do Admin</h1>
        <?php if (isset($error)): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <label>Usuário</label>
            <input type="text" name="user" required autofocus>
            <label>Senha</label>
            <input type="password" name="pass" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>