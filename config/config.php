<?php
// config/config.php
session_start();

// Paramètres de connexion (ajuste selon ton MySQL)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cybercafe');
define('DB_USER', 'root');
define('DB_PASS', ''); // mets ton mot de passe MySQL si tu en as un

// Chemin racine du projet (utile plus tard)
define('ROOT_PATH', realpath(__DIR__ . '/../'));
