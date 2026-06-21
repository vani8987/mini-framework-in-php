<?php
namespace Core;

use JsonException;

interface interfaceRequest {
    public function getDataBody(string $key): mixed;
    public function getDataJson(string $key): mixed;
    public function getDataUrl(string $key): mixed;
    public function getDataSession(string $key): mixed;
    public function getDataCookie(string $key): mixed;
}

class Request implements interfaceRequest {
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('system.log');
    }

    private function notEmpty(array $GlobalArray, string $key, string $name): bool {
        if (empty($GlobalArray)) {
            $this->logger->warning("The user did not send anything via $name.");
            return false;
        }
    
        if (!isset($GlobalArray[$key])) {
            $this->logger->warning("Key '$key' is not present in $name data.");
            return false;
        }

        return true;
    }

    public function getDataJson(string $key): mixed {
        $input = file_get_contents('php://input');

        if ($input === false || $input === '') {
            $this->logger->warning('The user did not send JSON data.');
            return null;
        }

        try {
            $jsonArray = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            $this->logger->error('JSON decoding failed: ' . $err->getMessage());
            return null;
        }

        if (!is_array($jsonArray)) {
            $this->logger->warning('JSON data must be an object.');
            return null;
        }

        if (!$this->notEmpty($jsonArray, $key, 'JSON')) {
            return null;
        }

        return $jsonArray[$key];
    }

    public function getDataBody(string $key): mixed {
        if (!$this->notEmpty($_POST, $key, "POST")) return null;

        return $_POST[$key];
    }

    public function getDataUrl(string $key): mixed {
        if (!$this->notEmpty($_GET, $key, "GET")) return null;

        return $_GET[$key];
    }

    public function getDataCookie(string $key): mixed {
        if (!$this->notEmpty($_COOKIE, $key, "COOKIES")) return null;

        return $_COOKIE[$key];
    }

    public function getDataSession(string $key): mixed {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->logger->error('Session is not available.');
            return null;
        }

        if (!$this->notEmpty($_SESSION, $key, 'SESSION')) return null;

        return $_SESSION[$key];
    }
    
}
