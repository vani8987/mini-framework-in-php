<?php

namespace Core;

class Controller
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('system.log');
    }
}
