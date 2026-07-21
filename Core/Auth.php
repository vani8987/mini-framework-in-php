<?php
namespace Core;

interface interfaceAuth {
    public function loginByEmail(string $password, string $mail): bool;

    public function registerByEmail(string $password, string $mail): bool;

    public function checkUser(): bool;

    public function logout(): bool;

    public function requireAuth(): void;

    public function user(array $columns): ?array;
}

interface UserProviderInterface {
    public function findByEmail(string $email): ?array;

    public function find(array $columns, int $id): ?array;

    public function create(array $nameColumns, array $values): bool;
}

class Auth implements interfaceAuth {
    private UserProviderInterface $modelBD;
    private Request $request;
    private Logger $logger;
    private string $passwordPepper;
    
    private function preparePassword(string $password): string
    {
        return hash_hmac('sha256', $password, $this->passwordPepper);
    }
    
    private function env(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key);
    
        return is_string($value) ? $value : '';
    }

    public function __construct(Request|UserProviderInterface $request, ?UserProviderInterface $modelBD = null, ?Logger $logger = null) {
        if ($request instanceof UserProviderInterface) {
            $modelBD = $request;
            $request = new Request();
        }

        if ($modelBD === null) {
            throw new \InvalidArgumentException('User provider is required.');
        }

        $this->modelBD = $modelBD;
        $this->request = $request;
        $this->logger = $logger ?? new Logger('auth.log');
        $this->passwordPepper = $this->env('HASH_KEY_PASSWORD');

        if ($this->passwordPepper === '') {
            throw new \RuntimeException('HASH_KEY_PASSWORD is not configured.');
        }
    }

    public function loginByEmail(string $password, string $mail): bool {
        if ($mail === '' || $password === '') {
            $this->logger->warning('Login attempt rejected: email or password is empty.');
            throw new \InvalidArgumentException('Email and password must be filled in.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $this->modelBD->findByEmail($mail);

        if ($user === null || !password_verify($this->preparePassword($password), $user['password'])) {
            $this->logger->warning("Login failed for email: {$mail}.");
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = $user['id'];
        $this->logger->info("User {$user['id']} logged in.");

        return true;
    }

    public function registerByEmail(string $password, string $mail): bool {
        if ($password === '' || $mail === '') {
            $this->logger->warning('Register attempt rejected: email or password is empty.');
            throw new \InvalidArgumentException('mail or password must be filled in');
        }

        if ($this->modelBD->findByEmail($mail) !== null) {
            $this->logger->warning("Register attempt rejected: email already exists: {$mail}.");
            return false;
        }

        $hashPassword = password_hash($this->preparePassword($password), PASSWORD_DEFAULT);

        $created = $this->modelBD->create(
            ['email', 'password'],
            [$mail, $hashPassword]
        );

        if ($created) {
            $this->logger->info("User registered with email: {$mail}.");
        } else {
            $this->logger->error("User registration failed for email: {$mail}.");
        }

        return $created;
    }

    public function checkUser(): bool
    {
        $userId = $this->request->getDataSession('auth_user_id');

        if ($userId === null) {
            $this->logger->info('Authentication check failed: user is not logged in.');
            return false;
        }

        $this->logger->info("Authentication check passed for user {$userId}.");
        return true;
    }

    public function user(array $columns): ?array {
        $this->requireAuth();
        $userId = $this->request->getDataSession('auth_user_id');

        if ($userId === null) {
            $this->logger->warning('User data requested without authentication.');
            throw new \RuntimeException('Unauthorized', 401);
        }

        $user = $this->modelBD->find($columns, (int) $userId);

        if ($user === null) {
            $this->logger->warning("User {$userId} was not found.");
            return null;
        }

        $this->logger->info("User {$userId} data requested.");
        return $user;
    }

    public function requireAuth(): void {
        if (!$this->checkUser()) {
            $this->logger->warning('Access denied: authentication is required.');
            throw new \RuntimeException('Unauthorized', 401);
        }

        $this->logger->info('Access granted to authenticated user.');
    }

    public function logout(): bool {
        if (!$this->checkUser()) {
            $this->logger->warning('Logout attempt rejected: user is not logged in.');
            return false;
        }

        $userId = $_SESSION['auth_user_id'];
        unset($_SESSION['auth_user_id']);
        $this->logger->info("User {$userId} logged out.");

        return true;
    }
}
