<?php
/**
 * @file
 * This starts the API endpoint of the FullPageDashboard app.
 * To see the frontend currently you need to visit /frontend/src/client/index.html
 */

require_once __DIR__ . '/vendor/autoload.php';

use twhiston\FullPageDashboard\FullPageDashboard;

$app = new FullPageDashboard(TRUE);
$app->registerServices();
$app->registerRoutes();
$app->run();