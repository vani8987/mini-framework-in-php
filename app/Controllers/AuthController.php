<?php

namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Request;
use Core\Response;

class AuthController
{
    private Auth $auth;
    private Request $request;
    private Response $response;

    public function __construct(?Auth $auth = null, ?Request $request = null, ?Response $response = null)
    {
        $this->request = $request ?? new Request();
        $this->auth = $auth ?? new Auth($this->request, new User());
        $this->response = $response ?? new Response();
    }

    public function register(): void
    {
        $email = $this->request->getDataJson('email');
        $password = $this->request->getDataJson('password');

        if (!is_string($email) || !is_string($password)) {
            $this->response->json(['message' => 'Email and password are required.'], 422);
            return;
        }

        if (!$this->auth->registerByEmail($password, $email)) {
            $this->response->json(['message' => 'Email is already in use.'], 409);
            return;
        }

        $this->response->json(['message' => 'User registered.'], 201);
    }

    public function login(): void
    {
        $email = $this->request->getDataJson('email');
        $password = $this->request->getDataJson('password');

        if (!is_string($email) || !is_string($password)) {
            $this->response->json(['message' => 'Email and password are required.'], 422);
            return;
        }

        if (!$this->auth->loginByEmail($password, $email)) {
            $this->response->json(['message' => 'Invalid credentials.'], 401);
            return;
        }

        $this->response->json(['message' => 'Logged in.']);
    }

    public function me(): void
    {
        $user = $this->auth->user(['id', 'email']);
        $this->response->json(['data' => $user]);
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->response->json(['message' => 'Logged out.']);
    }
}
