<?php
use database\MigrationManager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/database/migration.php';

$command = $argv[1] ?? null;

switch ($command) {

    case ('migrate:run'):
        $migration = new MigrationManager();
        $migration->run();
        break;

    case('migrate:down'):
        $migration = new MigrationManager();
        $migration->rollback();
        break;

    case ('migrate:fresh'):
        $migration = new MigrationManager();
        $migration->fresh();
        break;

    case('serve'):
        shell_exec("php -S localhost:8000 -t public");
        break;
    
    default:
        echo 'command not found';
        break;
}

