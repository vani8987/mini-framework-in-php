<?php

namespace App\Models;

use Core\CRUD;

class Example extends CRUD
{
    public function __construct()
    {
        parent::__construct('examples');
    }
}
