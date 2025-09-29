<?php

namespace App\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;
    private string $logFile;
    private string $logLevel;

    public function __construct(string $logFile = null, string $logLevel = 'info')
    {
        $this->logFile = $logFile ?? ($_ENV['LOG_FILE'] ?? 'logs/app.log');
        $this->logLevel = $logLevel ?? ($_ENV['LOG_LEVEL'] ?? 'info');
        
        $this->initializeLogger();
    }

    private function initializeLogger(): void
    {
        $this->logger = new MonologLogger('sheet-cast');
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Add rotating file handler (creates new file daily)
        $fileHandler = new RotatingFileHandler($this->logFile, 30, $this->getLogLevel());
        
        // Set custom formatter
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );
        $fileHandler->setFormatter($formatter);
        
        $this->logger->pushHandler($fileHandler);
    }

    private function getLogLevel(): int
    {
        return match (strtolower($this->logLevel)) {
            'debug' => MonologLogger::DEBUG,
            'info' => MonologLogger::INFO,
            'notice' => MonologLogger::NOTICE,
            'warning' => MonologLogger::WARNING,
            'error' => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            'alert' => MonologLogger::ALERT,
            'emergency' => MonologLogger::EMERGENCY,
            default => MonologLogger::INFO,
        };
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($this->getLogLevel(), $message, $context);
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function getLogLevelName(): string
    {
        return $this->logLevel;
    }
}
