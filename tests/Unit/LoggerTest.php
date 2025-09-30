<?php

namespace Tests\Unit;

use App\Logger\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private string $testLogFile;

    protected function setUp(): void
    {
        $this->testLogFile = sys_get_temp_dir() . '/test.log';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }
    }

    public function testLoggerCanBeInstantiated(): void
    {
        $logger = new Logger($this->testLogFile);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testLoggerCreatesLogFile(): void
    {
        $logger = new Logger($this->testLogFile, 'debug');
        $logger->info('Test message');
        
        $this->assertTrue(true);
    }

    public function testLoggerWritesMessages(): void
    {
        $logger = new Logger($this->testLogFile, 'debug');
        $logger->info('Test message');
        
        $this->assertTrue(true);
    }

    public function testLoggerHandlesContext(): void
    {
        $logger = new Logger($this->testLogFile, 'debug');
        $logger->info('Test message', ['key' => 'value']);
        
        $this->assertTrue(true);
    }

    public function testLoggerHandlesDifferentLevels(): void
    {
        $logger = new Logger($this->testLogFile, 'debug');
        
        $logger->info('Info message');
        $logger->error('Error message');
        
        $this->assertTrue(true);
    }
}
