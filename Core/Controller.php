<?php

namespace Core;

class Controller
{
    protected Logger $logger;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger('system.log');
    }
}
