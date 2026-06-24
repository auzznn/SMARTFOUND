<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

// ─── Composer Autoload ───────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

// Populate $_ENV from getenv() to ensure Apache/PHP-FPM web requests can read Docker environment variables
foreach (getenv() as $key => $value) {
    $_ENV[$key] = $value;
}

// ─── Load .env ───────────────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// ─── Build PHP-DI Container ──────────────────────────────────────────────────
$containerBuilder = new ContainerBuilder();

// Compile container in production for performance
if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

$addDefinitions = require __DIR__ . '/../config/container.php';
$addDefinitions($containerBuilder);

$container = $containerBuilder->build();

// ─── Create Slim App ─────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

// ─── Base Path ───────────────────────────────────────────────────────────────
$app->setBasePath('');

// ─── CORS Middleware (global, runs first) ────────────────────────────────────
$app->add(\App\Application\Middleware\CorsMiddleware::class);

// ─── Body Parsing Middleware ──────────────────────────────────────────────────
$app->addBodyParsingMiddleware();

// ─── Routing Middleware ───────────────────────────────────────────────────────
$app->addRoutingMiddleware();

// ─── Global Error Handler (JSON only — never HTML) ───────────────────────────
$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: ($_ENV['APP_ENV'] ?? 'development') !== 'production',
    logErrors: true,
    logErrorDetails: true
);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

// Custom JSON error handler
$errorMiddleware->setDefaultErrorHandler(
    function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app): \Psr\Http\Message\ResponseInterface {
        $statusCode = 500;
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
            $statusCode = 405;
        } elseif ($exception instanceof \Slim\Exception\HttpUnauthorizedException) {
            $statusCode = 401;
        } elseif ($exception instanceof \Slim\Exception\HttpForbiddenException) {
            $statusCode = 403;
        }

        $response = $app->getResponseFactory()->createResponse($statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        $payload = [
            'success' => false,
            'error'   => $exception->getMessage() ?: 'An error occurred',
            'message' => $displayErrorDetails ? $exception->getMessage() : 'Internal server error',
        ];

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response;
    }
);

// ─── Static File Route for Uploads ───────────────────────────────────────────
$app->get('/uploads/{filename}', function (
    \Psr\Http\Message\ServerRequestInterface $request,
    \Psr\Http\Message\ResponseInterface $response,
    array $args
): \Psr\Http\Message\ResponseInterface {
    $settings    = $this->get('settings');
    $uploadDir   = $settings['upload']['dir'];

    // Resolve absolute path
    if (!str_starts_with($uploadDir, '/') && !preg_match('/^[A-Z]:/i', $uploadDir)) {
        $uploadDir = __DIR__ . '/../' . $uploadDir;
    }

    $filename = basename($args['filename']); // Prevent path traversal
    $filepath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!file_exists($filepath) || !is_file($filepath)) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => 'File not found',
            'message' => 'The requested file does not exist.',
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($filepath);

    $stream = new \Slim\Psr7\Stream(fopen($filepath, 'rb'));
    return $response
        ->withBody($stream)
        ->withHeader('Content-Type', $mimeType)
        ->withHeader('Content-Length', (string)filesize($filepath))
        ->withHeader('Cache-Control', 'public, max-age=86400');
});

// ─── Catch-all OPTIONS Route for CORS Preflight ──────────────────────────────
$app->options('/{routes:.+}', function (
    \Psr\Http\Message\ServerRequestInterface $request,
    \Psr\Http\Message\ResponseInterface $response
): \Psr\Http\Message\ResponseInterface {
    return $response;
});

// ─── Register Route Files ─────────────────────────────────────────────────────
(require __DIR__ . '/../src/Application/Routes/auth.php')($app);
(require __DIR__ . '/../src/Application/Routes/reports.php')($app);
(require __DIR__ . '/../src/Application/Routes/comments.php')($app);
(require __DIR__ . '/../src/Application/Routes/users.php')($app);
(require __DIR__ . '/../src/Application/Routes/meta.php')($app);

// ─── Run ─────────────────────────────────────────────────────────────────────
$app->run();
