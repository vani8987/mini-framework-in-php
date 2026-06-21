<?php

namespace Core;

use DateTimeImmutable;
use InvalidArgumentException;

interface LoggerInterface
{
    public function info(string $message): void;
    public function warning(string $message): void;
    public function error(string $message): void;
}

class Logger implements LoggerInterface
{
    private string $nameLog;
    private string $directory;

    public function __construct(string $nameLog)
    {
        if (!preg_match('/^[A-Za-z0-9_-]+\.log$/', $nameLog)) {
            throw new InvalidArgumentException('The log file name must end with .log.');
        }

        $this->nameLog = $nameLog;
        $this->directory = dirname(__DIR__) . '/log';
    }

    private function getTimeDay(): string
    {
        $datetime = new DateTimeImmutable();
        return $datetime->format('Y-m-d H:i:s');
    }

    private function connectFile(string $message): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            error_log('Unable to create the log directory.');
            return;
        }

        $fullPath = $this->directory . '/' . $this->nameLog;

        if (file_put_contents($fullPath, $message . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log('Unable to write to the log file.');
        }
    }

    private function log(string $level, string $message): void
    {
        $date = $this->getTimeDay();
        $fullString = "[$date]|[$level] $message";

        $this->connectFile($fullString);
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }
}
