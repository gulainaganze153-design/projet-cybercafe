<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/DB.php';

try {
  $pdo = DB::get();
  echo "<h2 style='color:green'>✅ Connexion à la base réussie !</h2>";
} catch (Exception $e) {
  echo "<h2 style='color:red'>❌ Erreur : " . $e->getMessage() . "</h2>";
}
