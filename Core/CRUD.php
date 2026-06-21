<?php
namespace Core;

use Exception;
use InvalidArgumentException;
use PDO;


interface ModelInterface {
    public function create(array $nameColumn, array $arrValues): bool;
    public function delete(int $id): bool;
    public function find(array $nameColumns, int $id): ?array;
    public function update(array $nameColumns, array $arrValues, int $id): bool;
    public function findAll(array $nameColumns): array;
}

class CRUD extends ConnectDB implements ModelInterface  {
    private string $name;

    public function __construct(string $name) {
        parent::__construct();
        $this->name = $this->quoteIdentifier($name);

    }

    private function validate(array $names, array $values): bool
    {
        if (empty($names)) {
            $this->logger->warning('CRUD validation failed: columns are empty.');
            return false;
        }

        if (empty($values)) {
            $this->logger->warning('CRUD validation failed: values are empty.');
            return false;
        }

        if (count($names) !== count($values)) {
            $this->logger->warning('CRUD validation failed: column and value counts differ.');
            return false;
        }

        return true;
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid SQL identifier: {$identifier}");
        }

        return "`{$identifier}`";
    }

    private function quoteIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            throw new InvalidArgumentException('The columns cannot be empty.');
        }

        return array_map(function (mixed $identifier): string {
            if ($identifier === '*') {
                return '*';
            }

            if (!is_string($identifier)) {
                throw new InvalidArgumentException('The column name must be a string.');
            }

            return $this->quoteIdentifier($identifier);
        }, $identifiers);
    }

    public function create(array $nameColumn, array $arrValues): bool {
        if (! $this->validate($nameColumn, $arrValues)) {
            return false;
        }

        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumn));
            $values = implode(', ', array_fill(0, count($arrValues), '?'));

            $statement = $this->pdo->prepare("INSERT INTO {$this->name} ({$columns}) VALUES ({$values})");
            $statement->execute(array_values($arrValues));
            $this->logger->info("Record created in {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD create failed: ' . $err->getMessage());
            return false;
        }
    }

    public function update(array $nameColumns, array $arrValues, int $id): bool {
        if (! $this->validate($nameColumns, $arrValues)) {
            return false;
        }

        try {
            $columns = $this->quoteIdentifiers($nameColumns);

            for ($index = 0; $index < count($columns); $index++) {
                $statement = $this->pdo->prepare("UPDATE {$this->name} SET {$columns[$index]} = ? WHERE `id` = ?");
                $statement->execute([$arrValues[$index], $id]);
            }

            $this->logger->info("Record updated in {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD update failed: ' . $err->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $statement = $this->pdo->prepare("DELETE FROM {$this->name} WHERE `id` = ?");
            $statement->execute([$id]);
            $this->logger->info("Record deleted from {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD delete failed: ' . $err->getMessage());
            return false;
        }
    }

    public function find(array $nameColumns, int $id): ?array {
        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumns));

            $statement = $this->pdo->prepare("SELECT {$columns} FROM {$this->name} WHERE `id` = ?");
            $statement->execute([$id]);

            $this->logger->info("Record requested from {$this->name}.");
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result === false ? null : $result;
        } catch (Exception $err) {
            $this->logger->error('CRUD find failed: ' . $err->getMessage());
            return null;
        }
    }

    public function findAll(array $nameColumns): array {
        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumns));

            $statement = $this->pdo->query("SELECT {$columns} FROM {$this->name}");

            $this->logger->info("All records requested from {$this->name}.");
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            $this->logger->error('CRUD findAll failed: ' . $err->getMessage());
            return [];
        }
    }

}
