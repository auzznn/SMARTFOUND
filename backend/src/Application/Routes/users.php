<?php

declare(strict_types=1);

use App\Application\Actions\User\DeleteUserAction;
use App\Application\Actions\User\GetProfileAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\UpdateProfileAction;
use App\Application\Middleware\JwtMiddleware;
use App\Application\Middleware\RoleMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api/v1/users', function (RouteCollectorProxy $group): void {

        // GET /api/v1/users/me — current user's profile
        $group->get('/me',    GetProfileAction::class);
        $group->patch('/me',  UpdateProfileAction::class);

        // Admin-only endpoints
        $group->get('', ListUsersAction::class)
            ->add(new RoleMiddleware(['admin']));

        $group->delete('/{id:[0-9]+}', DeleteUserAction::class)
            ->add(new RoleMiddleware(['admin']));

    })->add(JwtMiddleware::class);
};
