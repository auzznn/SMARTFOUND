<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host     = $_ENV['DB_HOST'];
$port     = $_ENV['DB_PORT'];
$dbname   = $_ENV['DB_NAME'];
$user     = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    echo "Successfully connected to Supabase!\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}
