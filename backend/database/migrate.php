<?php
// backend/database/migrate.php

// Load Composer autoloader for any env utilities
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Safely load environment variables from local .env if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port   = $_ENV['DB_PORT'] ?? '5432';
$dbName = $_ENV['DB_NAME'] ?? 'postgres';
$user   = $_ENV['DB_USER'] ?? 'postgres';
$pass   = $_ENV['DB_PASS'] ?? '';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbName";

// Database availability wait & retry loop (essential for multi-container startup orchestration)
$maxTries = 10;
$try = 1;
$pdo = null;

while ($try <= $maxTries) {
    try {
        echo "[Migration] Connecting to database at $host:$port (attempt $try/$maxTries)...\n";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        break; // Success
    } catch (PDOException $e) {
        echo "[Migration] Connection attempt $try failed: " . $e->getMessage() . "\n";
        if ($try === $maxTries) {
            echo "[Migration] Could not connect to database. Exiting.\n";
            exit(1);
        }
        $try++;
        sleep(3); // wait 3 seconds before next retry
    }
}

// Safe check: Check if the 'users' table already exists in the database
// We use public.users schema search to check for existence
try {
    $query = $pdo->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'users'
    )");
    $tablesExist = $query->fetchColumn();
} catch (PDOException $e) {
    echo "[Migration] Error checking database tables: " . $e->getMessage() . "\n";
    exit(1);
}

if ($tablesExist) {
    echo "[Migration] Database tables already exist. Skipping schema initialization to prevent data loss.\n";
    exit(0);
}

echo "[Migration] Empty database detected. Initiating schema creation...\n";

// Execute schema.sql
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    echo "[Migration] Error: schema.sql not found at $schemaFile\n";
    exit(1);
}

$schemaSql = file_get_contents($schemaFile);
try {
    $pdo->exec($schemaSql);
    echo "[Migration] Database schema applied successfully.\n";
} catch (PDOException $e) {
    echo "[Migration] Error applying database schema: " . $e->getMessage() . "\n";
    exit(1);
}

// Execute seed.sql
$seedFile = __DIR__ . '/seed.sql';
if (file_exists($seedFile)) {
    echo "[Migration] Seeding database with default records...\n";
    $seedSql = file_get_contents($seedFile);
    try {
        $pdo->exec($seedSql);
        echo "[Migration] Database seeded successfully.\n";
    } catch (PDOException $e) {
        echo "[Migration] Error seeding database: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "[Migration] Warning: seed.sql not found. Skipping seeding.\n";
}

echo "[Migration] Database initialization complete! ✅\n";
