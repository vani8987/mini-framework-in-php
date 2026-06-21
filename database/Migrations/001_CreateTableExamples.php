<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableExamples extends CreateTable
{
    public function __construct()
    {
        parent::__construct('examples');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'title VARCHAR(255) NOT NULL',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
