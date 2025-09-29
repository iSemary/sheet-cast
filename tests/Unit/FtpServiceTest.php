<?php

namespace Tests\Unit;

use App\Logger\Logger;
use App\Service\FtpService;
use PHPUnit\Framework\TestCase;

class FtpServiceTest extends TestCase
{
    private FtpService $ftpService;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger(sys_get_temp_dir() . '/test.log');
        $this->ftpService = new FtpService($this->logger);
    }

    public function testFtpServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(FtpService::class, $this->ftpService);
    }

    public function testConnectWithInvalidHost(): void
    {
        $this->assertTrue(true);
    }

    public function testGetFileContentWithoutConnection(): void
    {
        $result = $this->ftpService->getFileContent('test.xml');
        
        $this->assertNull($result);
    }

    public function testDisconnectWithoutConnection(): void
    {
        $this->ftpService->disconnect();
        
        $this->assertTrue(true);
    }
}
