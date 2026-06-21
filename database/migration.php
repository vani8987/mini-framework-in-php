<?php
namespace database;

require_once __DIR__ . '/../vendor/autoload.php';

use Core\ConnectDB;
use PDO;

interface MigrationInterface {
    public function run(): void;
}

class MigrationManager extends ConnectDB implements MigrationInterface {
    private string $dirPath;
    
    public function __construct() {
        parent::__construct();
        $this->dirPath = __DIR__ . '/Migrations';
        $this->createMigrationsTable();
    }

    private function getAllFiles(): array | null {
        $allFiles = array_diff(
            scandir($this->dirPath),
            ['.', '..']
        );

        $allFiles = array_filter($allFiles, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });

        if (empty($allFiles)) {
            echo "migration files not found\n";
            return null;
        }

        sort($allFiles);

        return $allFiles;
    }

    private function makeMigration(string $file): object {
        require_once $this->dirPath . '/' . $file;

        $className = pathinfo($file, PATHINFO_FILENAME);
        $className = preg_replace('/^\d+_/', '', $className);
        $fullClassName = 'database\\Migrations\\' . $className;

        return new $fullClassName();
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL UNIQUE,
                `batch` INT NOT NULL,
                `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        );
    }

    private function hasMigration(string $file): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM `migrations` WHERE `name` = ?');
        $statement->execute([$file]);

        return $statement->fetchColumn() !== false;
    }

    private function getNextBatch(): int
    {
        $batch = $this->pdo->query('SELECT MAX(`batch`) FROM `migrations`')->fetchColumn();

        return $batch === null ? 1 : (int) $batch + 1;
    }

    private function saveMigration(string $file, int $batch): void
    {
        $statement = $this->pdo->prepare('INSERT INTO `migrations` (`name`, `batch`) VALUES (?, ?)');
        $statement->execute([$file, $batch]);
    }

    private function getLatestBatch(): ?int
    {
        $batch = $this->pdo->query('SELECT MAX(`batch`) FROM `migrations`')->fetchColumn();

        return $batch === null ? null : (int) $batch;
    }

    private function getAppliedMigrations(?int $batch = null): array
    {
        if ($batch === null) {
            $statement = $this->pdo->query('SELECT `name` FROM `migrations` ORDER BY `batch` DESC, `id` DESC');
        } else {
            $statement = $this->pdo->prepare(
                'SELECT `name` FROM `migrations` WHERE `batch` = ? ORDER BY `id` DESC'
            );
            $statement->execute([$batch]);
        }

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function deleteMigration(string $file): void
    {
        $statement = $this->pdo->prepare('DELETE FROM `migrations` WHERE `name` = ?');
        $statement->execute([$file]);
    }

    private function rollbackMigrations(array $files): void
    {
        foreach ($files as $file) {
            try {
                $migration = $this->makeMigration($file);
                $migration->down();
                $this->deleteMigration($file);

                $this->logger->info("Migration rolled back: {$file}.");
                echo "migration {$file} deleted\n";
            } catch (\Throwable $err) {
                $this->logger->error("Migration rollback failed for {$file}: " . $err->getMessage());
                throw $err;
            }
        }
    }

    public function run(): void {
        $allFiles = $this->getAllFiles();

        if ($allFiles === null) {
            echo "migration directory is empty\n";
            return;
        }

        $batch = $this->getNextBatch();

        foreach ($allFiles as $file) {
            if ($this->hasMigration($file)) {
                continue;
            }

            try {
                $migration = $this->makeMigration($file);
                $migration->up();
                $this->saveMigration($file, $batch);

                $this->logger->info("Migration applied: {$file}.");
                echo "migration {$file} created\n";
            } catch (\Throwable $err) {
                $this->logger->error("Migration failed for {$file}: " . $err->getMessage());
                throw $err;
            }
        }
    }

    public function rollback(): void {
        $batch = $this->getLatestBatch();

        if ($batch === null) {
            echo "no migrations to rollback\n";
            return;
        }

        $this->rollbackMigrations($this->getAppliedMigrations($batch));
    }

    public function fresh(): void {
        $allFiles = $this->getAllFiles();

        if ($allFiles === null) {
            echo "migration directory is empty\n";
            return;
        }

        $this->rollbackMigrations($this->getAppliedMigrations());
        $this->run();
    }
}
