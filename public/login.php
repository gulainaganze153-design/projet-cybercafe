<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/DB.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else $error = "Nom d'utilisateur ou mot de passe incorrect";
    } else $error = "Remplis tous les champs";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion - CyberCafé</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Orbitron', sans-serif; background:#0b0b0f; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:#1a1a2e; padding:40px; border-radius:15px; box-shadow:0 0 30px #00fff7; width:350px; text-align:center; }
h2 { color:#00fff7; text-shadow:0 0 10px #00fff7; margin-bottom:30px; }
input { width:100%; padding:12px; margin:10px 0; border:none; border-radius:8px; background:#121212; color:#fff; }
input:focus { outline:none; box-shadow:0 0 8px #00fff7; }
button { width:100%; padding:12px; background:#00fff7; border:none; border-radius:8px; color:#0b0b0f; font-weight:bold; cursor:pointer; transition:0.3s; }
button:hover { box-shadow:0 0 15px #00fff7; transform:scale(1.05); }
.error { color:#ff0033; margin-bottom:15px; }
a { color:#00fff7; text-decoration:none; font-size:0.9em; display:block; margin-top:15px; }
a:hover { text-shadow:0 0 8px #00fff7; }
</style>
</head>
<body>
<div class="container">
  <h2>Connexion</h2>
  <?php if($error) echo "<div class='error'>$error</div>"; ?>
  <form method="post">
    <input type="text" name="username" placeholder="Utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
  </form>
  <a href="register.php">Créer un compte</a>
</div>
</body>
</html>
