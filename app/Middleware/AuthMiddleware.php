<?php

namespace App\Middleware;

use Core\Middleware;
use App\Models\User;
use Core\Logger;
use Core\Request;

class AuthMiddleware extends Middleware {
    private User $user;

    public function __construct(?Request $request = null, ?Logger $logger = null, ?User $user = null) {
        parent::__construct($request, $logger);

        $this->user = $user ?? new User();
    }

    public function userAuth(): bool {
        $userId = $this->request->getDataSession('auth_user_id');

        if ($userId === null || !is_numeric($userId)) {
            $this->logger->warning('Authentication failed: user session is empty.');
            return false;
        }

        $userIdInModel = $this->user->find(['id'], (int) $userId);

        if ($userIdInModel === null) {
            $this->logger->warning("Authentication failed: user {$userId} was not found.");
            return false;
        }

        $this->logger->info("Authentication passed for user {$userId}.");
        return true;
    }
}
