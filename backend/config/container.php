<?php

declare(strict_types=1);

use App\Application\Actions\Auth\GoogleCallbackAction;
use App\Application\Actions\Auth\GoogleRedirectAction;
use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Auth\LogoutAction;
use App\Application\Actions\Auth\RefreshTokenAction;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Actions\Comment\CreateCommentAction;
use App\Application\Actions\Comment\ListCommentsAction;
use App\Application\Actions\Meta\ListCategoriesAction;
use App\Application\Actions\Meta\ListLocationsAction;
use App\Application\Actions\Report\CreateReportAction;
use App\Application\Actions\Report\DeleteReportAction;
use App\Application\Actions\Report\GetReportAction;
use App\Application\Actions\Report\ListClosedReportsAction;
use App\Application\Actions\Report\ListMyReportsAction;
use App\Application\Actions\Report\ListReportsAction;
use App\Application\Actions\Report\UpdateReportStatusAction;
use App\Application\Actions\User\DeleteUserAction;
use App\Application\Actions\User\GetProfileAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\UpdateProfileAction;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Auth\GoogleOAuthService;
use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\PdoCommentRepository;
use App\Infrastructure\Persistence\PdoItemRepository;
use App\Infrastructure\Persistence\PdoReportRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([

        // Settings
        'settings' => fn() => require __DIR__ . '/settings.php',

        // PDO
        PDO::class => function (ContainerInterface $c): PDO {
            $s = $c->get('settings')['db'];
            $dsn = "pgsql:host={$s['host']};port={$s['port']};dbname={$s['name']}";
            $pdo = new PDO($dsn, $s['user'], $s['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        },

        // Services
        JwtService::class => function (ContainerInterface $c): JwtService {
            $jwt = $c->get('settings')['jwt'];
            return new JwtService($jwt['secret'], $jwt['access_ttl'], $jwt['refresh_ttl']);
        },

        GoogleOAuthService::class => function (ContainerInterface $c): GoogleOAuthService {
            $g = $c->get('settings')['google'];
            return new GoogleOAuthService($g['client_id'], $g['client_secret'], $g['redirect_uri']);
        },

        // Repositories
        PdoUserRepository::class => fn(ContainerInterface $c) => new PdoUserRepository($c->get(PDO::class)),
        PdoReportRepository::class => fn(ContainerInterface $c) => new PdoReportRepository($c->get(PDO::class), $c->get('settings')['app_url']),
        PdoCommentRepository::class => fn(ContainerInterface $c) => new PdoCommentRepository($c->get(PDO::class)),
        PdoItemRepository::class => fn(ContainerInterface $c) => new PdoItemRepository($c->get(PDO::class)),

        // Repository interfaces
        UserRepositoryInterface::class => fn(ContainerInterface $c) => $c->get(PdoUserRepository::class),
        ReportRepositoryInterface::class => fn(ContainerInterface $c) => $c->get(PdoReportRepository::class),

        // Auth Actions
        RegisterAction::class => fn(ContainerInterface $c) => new RegisterAction(
            $c->get(PdoUserRepository::class),
            $c->get(JwtService::class)
        ),
        LoginAction::class => fn(ContainerInterface $c) => new LoginAction(
            $c->get(PdoUserRepository::class),
            $c->get(JwtService::class)
        ),
        LogoutAction::class => fn(ContainerInterface $c) => new LogoutAction(),
        RefreshTokenAction::class => fn(ContainerInterface $c) => new RefreshTokenAction(
            $c->get(JwtService::class),
            $c->get(PdoUserRepository::class)
        ),
        GoogleRedirectAction::class => fn(ContainerInterface $c) => new GoogleRedirectAction(
            $c->get(GoogleOAuthService::class)
        ),
        GoogleCallbackAction::class => fn(ContainerInterface $c) => new GoogleCallbackAction(
            $c->get(GoogleOAuthService::class),
            $c->get(PdoUserRepository::class),
            $c->get(JwtService::class),
            $c->get('settings')['frontend_url']
        ),

        // Report Actions
        ListReportsAction::class => fn(ContainerInterface $c) => new ListReportsAction(
            $c->get(PdoReportRepository::class)
        ),
        ListClosedReportsAction::class => fn(ContainerInterface $c) => new ListClosedReportsAction(
            $c->get(PdoReportRepository::class)
        ),
        ListMyReportsAction::class => fn(ContainerInterface $c) => new ListMyReportsAction(
            $c->get(PdoReportRepository::class)
        ),
        CreateReportAction::class => fn(ContainerInterface $c) => new CreateReportAction(
            $c->get(PdoReportRepository::class),
            $c->get(PdoItemRepository::class),
            $c->get(PdoUserRepository::class),
            $c->get('settings')['upload'],
            $c->get('settings')['app_url']
        ),
        GetReportAction::class => fn(ContainerInterface $c) => new GetReportAction(
            $c->get(PdoReportRepository::class)
        ),
        UpdateReportStatusAction::class => fn(ContainerInterface $c) => new UpdateReportStatusAction(
            $c->get(PdoReportRepository::class)
        ),
        DeleteReportAction::class => fn(ContainerInterface $c) => new DeleteReportAction(
            $c->get(PdoReportRepository::class),
            $c->get('settings')['upload']
        ),

        // Comment Actions
        ListCommentsAction::class => fn(ContainerInterface $c) => new ListCommentsAction(
            $c->get(PdoCommentRepository::class)
        ),
        CreateCommentAction::class => fn(ContainerInterface $c) => new CreateCommentAction(
            $c->get(PdoCommentRepository::class),
            $c->get(PdoReportRepository::class)
        ),

        // User Actions
        GetProfileAction::class => fn(ContainerInterface $c) => new GetProfileAction(
            $c->get(PdoUserRepository::class)
        ),
        UpdateProfileAction::class => fn(ContainerInterface $c) => new UpdateProfileAction(
            $c->get(PdoUserRepository::class)
        ),
        ListUsersAction::class => fn(ContainerInterface $c) => new ListUsersAction(
            $c->get(PdoUserRepository::class)
        ),
        DeleteUserAction::class => fn(ContainerInterface $c) => new DeleteUserAction(
            $c->get(PdoUserRepository::class)
        ),

        // Meta Actions
        ListCategoriesAction::class => fn(ContainerInterface $c) => new ListCategoriesAction(
            $c->get(PDO::class)
        ),
        ListLocationsAction::class => fn(ContainerInterface $c) => new ListLocationsAction(
            $c->get(PDO::class)
        ),
    ]);
};
