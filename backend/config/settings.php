<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? 'smartfound',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    'jwt' => [
        'secret'      => $_ENV['APP_SECRET'] ?? 'changeme',
        'access_ttl'  => 900,       // 15 minutes
        'refresh_ttl' => 604800,    // 7 days
    ],
    'upload' => [
        'dir'      => $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/../uploads',
        'max_size' => (int)($_ENV['MAX_FILE_SIZE'] ?? 2097152),
    ],
    'google' => [
        'client_id'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => $_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost:8080/api/v1/auth/google/callback',
    ],
    'app_url'      => $_ENV['APP_URL']      ?? 'http://localhost:8080',
    'frontend_url' => $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173',
];
