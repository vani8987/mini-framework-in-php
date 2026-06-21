<?php

use App\Controllers\ExampleController;
use Core\Router;

Router::route('/api/examples', 'GET', [ExampleController::class, 'index']);
Router::route('/api/examples', 'POST', [ExampleController::class, 'store']);
