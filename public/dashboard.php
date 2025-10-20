<?php
require_once __DIR__ . '/../config/config.php';
if(session_status()==PHP_SESSION_NONE) session_start();
if(empty($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$username=$_SESSION['username']??'Utilisateur';
$role=$_SESSION['role']??'';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard - CyberCafé</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{margin:0;font-family:'Orbitron',sans-serif;background:#0b0b0f;color:#fff;}
.container{max-width:1000px;margin:50px auto;padding:20px;background:#1a1a2e;border-radius:15px;box-shadow:0 0 30px #00fff7;}
h2{text-align:center;color:#00fff7;text-shadow:0 0 10px #00fff7;margin-bottom:20px;}
p{text-align:center;color:#fff;}
.menu{display:flex;justify-content:space-around;flex-wrap:wrap;margin-top:30px;}
.card{background:#272727;border-radius:12px;padding:20px;width:200px;margin:10px;text-align:center;transition:0.3s;cursor:pointer;box-shadow:0 0 10px #00fff722;}
.card:hover{box-shadow:0 0 20px #00fff7;transform:translateY(-5px);}
.card a{color:#00fff7;text-decoration:none;font-weight:bold;}
.card a:hover{text-shadow:0 0 8px #00fff7;}
.logout{margin-top:40px;text-align:center;}
.logout button{background:#00fff7;color:#0b0b0b;border:none;padding:12px 25px;border-radius:10px;font-weight:bold;cursor:pointer;transition:0.3s;}
.logout button:hover{box-shadow:0 0 15px #00fff7;transform:scale(1.05);}
</style>
</head>
<body>
<div class="container">
<h2>Bienvenue, <?= htmlspecialchars($username) ?> !</h2>
<p>Rôle : <?= htmlspecialchars($role) ?></p>
<div class="menu">
<div class="card"><a href="clients.php">Gérer les clients</a></div>
<div class="card"><a href="postes.php">Gérer les postes</a></div>
<div class="card"><a href="reservations.php">Réservations</a></div>
</div>
<div class="logout">
<form action="logout.php" method="post">
<button type="submit">Se déconnecter</button>
</form>
</div>
</div>
</body>
</html>
