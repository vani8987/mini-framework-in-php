<?php
namespace Core;
use Core\ConnectDB;

class CreateTable extends ConnectDB {
    private string $name;

    public function __construct(string $name, ?Logger $logger = null) {
        parent::__construct($logger);
        $this->name = $name;
    }

    public function createTable(array $arrColumns): void {
        if (empty($arrColumns)) {
            $this->logger->warning("Table creation skipped: '{$this->name}' has no columns.");
            echo 'there must be at least one column';
            return;
        }

        $sqlColumns = implode(",\n", $arrColumns);

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `$this->name` ($sqlColumns)");
            $this->logger->info("Table created or already exists: '{$this->name}'.");
            echo 'table created';
        } catch (\Throwable $err) {
            $this->logger->error("Table creation failed for '{$this->name}': " . $err->getMessage());
            throw $err;
        }
    }


    public function addColumn(string $column): void {
        try {
            $this->pdo->exec("ALTER TABLE `$this->name` ADD $column");
            $this->logger->info("Column added to table: '{$this->name}'.");
            echo 'column added';
        } catch (\Throwable $err) {
            $this->logger->error("Column addition failed for '{$this->name}': " . $err->getMessage());
            throw $err;
        }
    }
    

    public function dropTable(): void {
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS `$this->name`");
            $this->logger->info("Table deleted: '{$this->name}'.");
            echo 'table deleted';
        } catch (\Throwable $err) {
            $this->logger->error("Table deletion failed for '{$this->name}': " . $err->getMessage());
            throw $err;
        }
    }
}
