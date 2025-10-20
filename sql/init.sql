CREATE DATABASE IF NOT EXISTS cybercafe CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cybercafe;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fullname VARCHAR(100),
  role ENUM('admin','cashier') DEFAULT 'cashier',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(30),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS postes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(50) NOT NULL,
  status ENUM('free','occupied','maintenance') DEFAULT 'free',
  rate_per_hour DECIMAL(6,2) NOT NULL DEFAULT 1.00
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT,
  poste_id INT,
  start_time DATETIME DEFAULT NULL,
  end_time DATETIME DEFAULT NULL,
  total DECIMAL(10,2) DEFAULT 0,
  status ENUM('booked','in_progress','finished','cancelled') DEFAULT 'booked',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
  FOREIGN KEY (poste_id) REFERENCES postes(id) ON DELETE SET NULL
);
