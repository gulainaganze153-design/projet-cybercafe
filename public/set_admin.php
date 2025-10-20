<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/DB.php';

$hash = '$2y$10$0kYHgWDru1ycw8/avkzBQeNRc5I7F/juJ5UzB4h1Y5KE653SkMFde';
$username = 'admin';
$fullname = 'Administrateur';
$role = 'admin';

$pdo = DB::get();

// Vérifier si admin existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Met à jour le mot de passe
    $u = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $u->execute([$hash, $user['id']]);
    echo "Mot de passe mis à jour pour user 'admin'.";
} else {
    // Insère un nouvel admin
    $i = $pdo->prepare("INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, ?)");
    $i->execute([$username, $hash, $fullname, $role]);
    echo "Utilisateur 'admin' créé.";
}
