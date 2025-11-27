<?php
// db_connect.php
require_once 'config.php';

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

function testConnection() {
    try {
        $conn = getDBConnection();
        return "Connection successful! Database: " . DB_NAME;
    } catch (Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
}
?>