<?php

declare(strict_types=1);

use App\Application\Actions\Auth\GoogleCallbackAction;
use App\Application\Actions\Auth\GoogleRedirectAction;
use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Auth\LogoutAction;
use App\Application\Actions\Auth\RefreshTokenAction;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Middleware\JwtMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api/v1/auth', function (RouteCollectorProxy $group): void {
        // Public
        $group->post('/register', RegisterAction::class);
        $group->post('/login',    LoginAction::class);
        $group->post('/refresh',  RefreshTokenAction::class);

        // Google OAuth — public
        $group->get('/google/redirect', GoogleRedirectAction::class);
        $group->get('/google/callback', GoogleCallbackAction::class);

        // Protected
        $group->post('/logout', LogoutAction::class)
            ->add(JwtMiddleware::class);
    });
};
