<?php

namespace Core;

use Core\Request;
use Core\Logger;

class Middleware {
    protected Logger $logger;
    protected Request $request;

    public function __construct(){
        $this->logger = new Logger('Middleware.log');
        $this->request = new Request();
    }
}
