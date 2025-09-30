<?php

namespace App\Logger;

use App\Configuration\Configuration;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;
    private array $config;

    public function __construct(string $logFile = null, string $logLevel = null)
    {
        $this->config = Configuration::get('logs');
        
        // Override config with constructor parameters if provided
        if ($logFile !== null) {
            $this->config['file'] = $logFile;
        }
        if ($logLevel !== null) {
            $this->config['level'] = $logLevel;
        }
        
        $this->initializeLogger();
    }

    private function initializeLogger(): void
    {
        $this->logger = new MonologLogger('sheet-cast');
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->config['file']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Add rotating file handler (creates new file daily)
        $fileHandler = new RotatingFileHandler(
            $this->config['file'], 
            $this->config['max_files'], 
            $this->getLogLevel()
        );
        
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
        return match (strtolower($this->config['level'])) {
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

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
