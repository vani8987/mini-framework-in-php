<?php
namespace Core;
use Dotenv\Dotenv;
use PDO;

class ConnectDB {
    protected PDO $pdo;
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('system.log');
        $this->loadEnv();

        try {
            $this->pdo = $this->connectDB();
            $this->logger->info('Database connection established.');
        } catch (\Throwable $err) {
            $this->logger->error('Database connection failed: ' . $err->getMessage());
            throw $err;
        }
    }

    private function loadEnv(): void
    {
        $envPath = dirname(__DIR__);

        if (file_exists($envPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->safeLoad();
        }
    }

    private function connectDB(): PDO
    {
        $host = $this->env('DB_HOST');
        $port = $this->env('DB_PORT');
        $dbName = $this->env('DB_NAME');
        $userName = $this->env('DB_USER');
        $password = $this->env('DB_PASSWORD');

        return new PDO(
            "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4",
            $userName,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    private function env(string $key): string
    {
        return $_ENV[$key] ?? getenv($key) ?: '';
    }
}
