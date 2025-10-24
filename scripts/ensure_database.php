<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db = getenv('DB_DATABASE') ?: 'ksu_dorm';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$collation = getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci';

$dsn = sprintf('mysql:host=%s;port=%s', $host, $port);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $sql = sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s', $db, $charset, $collation);
    $pdo->exec($sql);
    echo "Database '$db' ensured." . PHP_EOL;
} catch (PDOException $e) {
    fwrite(STDERR, 'Failed to create database: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
