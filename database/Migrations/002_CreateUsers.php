<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateUsers extends CreateTable
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'email VARCHAR(255) NOT NULL UNIQUE',
            'password VARCHAR(255) NOT NULL',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
