<?php
/**
 * LOGIN.PHP — Page de connexion Admin
 */
require_once 'auth.php';
require_once 'db.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Identifiants invalides.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SoccerMidable</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #6A1B9A;
            --gold: #FFD700;
            --dark: #121212;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background: var(--dark);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: #1e1e1e;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--gold);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: #aaa; }
        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #252525;
            color: white;
            box-sizing: border-box;
            font-family: inherit;
        }
        .form-input:focus { border-color: var(--gold); outline: none; }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--purple);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1rem;
        }
        .btn-login:hover { background: #8E24AA; transform: translateY(-2px); }
        .error-msg {
            background: #c62828;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Panel</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Utilisateur</label>
                <input type="text" name="username" class="form-input" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn-login">SE CONNECTER</button>
        </form>
    </div>
</body>
</html>
