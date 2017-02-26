<?php
/**
 * Created by PhpStorm.
 * User: twhiston
 * Date: 25.02.17
 * Time: 16:03
 */

require_once __DIR__ . '/vendor/autoload.php';

use twhiston\FullPageDashboard\FullPageDashboard;

$app = new FullPageDashboard(TRUE);
$app->registerServices();
$app->registerRoutes();
$app->run();