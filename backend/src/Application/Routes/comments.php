<?php

declare(strict_types=1);

use App\Application\Actions\Comment\CreateCommentAction;
use App\Application\Actions\Comment\ListCommentsAction;
use App\Application\Middleware\JwtMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api/v1/reports/{id:[0-9]+}/comments', function (RouteCollectorProxy $group): void {
        $group->get('',  ListCommentsAction::class);
        $group->post('', CreateCommentAction::class);
    })->add(JwtMiddleware::class);
};
