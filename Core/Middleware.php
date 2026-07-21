<?php

namespace Core;

use Core\Request;
use Core\Logger;

class Middleware {
    protected Logger $logger;
    protected Request $request;

    public function __construct(?Request $request = null, ?Logger $logger = null){
        $this->logger = $logger ?? new Logger('Middleware.log');
        $this->request = $request ?? new Request();
    }
}
