<?php

declare(strict_types=1);

use App\Application\Actions\Report\CreateReportAction;
use App\Application\Actions\Report\DeleteReportAction;
use App\Application\Actions\Report\GetReportAction;
use App\Application\Actions\Report\ListClosedReportsAction;
use App\Application\Actions\Report\ListMyReportsAction;
use App\Application\Actions\Report\ListReportsAction;
use App\Application\Actions\Report\UpdateReportStatusAction;
use App\Application\Middleware\JwtMiddleware;
use App\Application\Middleware\RoleMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api/v1/reports', function (RouteCollectorProxy $group): void {

        // IMPORTANT: Static routes MUST be registered before parameterised {id} routes.
        // Slim matches in registration order — 'closed' and 'mine' would be swallowed
        // by {id} if registered after it.

        // GET /api/v1/reports/closed — list closed reports
        $group->get('/closed', ListClosedReportsAction::class);

        // GET /api/v1/reports/mine — authenticated user's own reports
        $group->get('/mine', ListMyReportsAction::class);

        // GET /api/v1/reports — open reports, filterable
        $group->get('', ListReportsAction::class);

        // POST /api/v1/reports — create new report (multipart/form-data)
        $group->post('', CreateReportAction::class);

        // GET /api/v1/reports/{id}
        $group->get('/{id:[0-9]+}', GetReportAction::class);

        // PATCH /api/v1/reports/{id}/status — owner only
        $group->patch('/{id:[0-9]+}/status', UpdateReportStatusAction::class);

        // DELETE /api/v1/reports/{id} — officer or admin only
        $group->delete('/{id:[0-9]+}', DeleteReportAction::class)
            ->add(new RoleMiddleware(['officer', 'admin']));

    })->add(JwtMiddleware::class);
};
