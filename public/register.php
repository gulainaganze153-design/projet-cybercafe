<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/DB.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $role     = trim($_POST['role'] ?? 'cashier');

    if(!$username || !$password) $errors[] = "Utilisateur et mot de passe obligatoires.";
    if($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";
    if(strlen($password) < 6) $errors[] = "Mot de passe minimum 6 caractères.";

    if(empty($errors)) {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $stmt->execute([$username]);
        if($stmt->fetch()) $errors[] = "Nom d'utilisateur déjà existant";
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username,password,fullname,role) VALUES(?,?,?,?)");
            $ins->execute([$username,$hash,$fullname,$role]);
            $success="Utilisateur créé avec succès !";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un compte - CyberCafé</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Orbitron', sans-serif; background:#0b0b0f; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:#1a1a2e; padding:40px; border-radius:15px; box-shadow:0 0 30px #00fff7; width:350px; text-align:center; }
h2 { color:#00fff7; text-shadow:0 0 10px #00fff7; margin-bottom:30px; }
input, select { width:100%; padding:12px; margin:10px 0; border:none; border-radius:8px; background:#121212; color:#fff; }
input:focus, select:focus { outline:none; box-shadow:0 0 8px #00fff7; }
button { width:100%; padding:12px; background:#00fff7; border:none; border-radius:8px; color:#0b0b0f; font-weight:bold; cursor:pointer; transition:0.3s; }
button:hover { box-shadow:0 0 15px #00fff7; transform:scale(1.05); }
.error { color:#ff0033; margin-bottom:15px; }
.success { color:#00ff99; margin-bottom:15px; }
a { color:#00fff7; text-decoration:none; font-size:0.9em; display:block; margin-top:15px; }
a:hover { text-shadow:0 0 8px #00fff7; }
</style>
</head>
<body>
<div class="container">
<h2>Créer un compte</h2>
<?php
if($errors){foreach($errors as $e) echo "<div class='error'>$e</div>";}
if($success) echo "<div class='success'>$success</div>";
?>
<form method="post">
<input type="text" name="username" placeholder="Nom d'utilisateur" required>
<input type="text" name="fullname" placeholder="Nom complet (optionnel)">
<input type="password" name="password" placeholder="Mot de passe" required>
<input type="password" name="confirm" placeholder="Confirmer mot de passe" required>
<select name="role">
  <option value="cashier">cashier</option>
  <option value="admin">admin</option>
</select>
<button type="submit">Créer le compte</button>
</form>
<a href="login.php">Déjà un compte ? Se connecter</a>
</div>
</body>
</html>
