<?php

use App\Controllers\AuthController;
use Core\Router;

Router::route('/auth/register', 'POST', [AuthController::class, 'register']);
Router::route('/auth/login', 'POST', [AuthController::class, 'login']);
Router::route('/auth/me', 'GET', [AuthController::class, 'me'], true);
Router::route('/auth/logout', 'POST', [AuthController::class, 'logout'], true);
