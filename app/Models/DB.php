<?php
// app/Models/DB.php

class DB {
  private static $pdo = null;

  public static function get() {
    if (self::$pdo === null) {
      $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
      try {
        self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
      } catch (PDOException $e) {
        die('❌ Erreur de connexion à la base : ' . $e->getMessage());
      }
    }
    return self::$pdo;
  }
}
