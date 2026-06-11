<?php

declare(strict_types=1);

use App\Application\Actions\Meta\ListCategoriesAction;
use App\Application\Actions\Meta\ListLocationsAction;
use App\Application\Middleware\JwtMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api/v1', function (RouteCollectorProxy $group): void {
        $group->get('/categories', ListCategoriesAction::class);
        $group->get('/locations',  ListLocationsAction::class);
    })->add(JwtMiddleware::class);
};
