<?php

namespace Tests\Unit;

use App\Logger\Logger;
use App\Service\GoogleSheetsService;
use PHPUnit\Framework\TestCase;

class GoogleSheetsServiceTest extends TestCase
{
    private GoogleSheetsService $googleSheetsService;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger(sys_get_temp_dir() . '/test.log');
        $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID'] = 'test-spreadsheet-id';
        $_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'] = sys_get_temp_dir() . '/test-credentials.json';
        file_put_contents($_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'], json_encode([
            'type' => 'service_account',
            'client_id' => 'test-client-id',
            'client_email' => 'test@example.com',
            'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7VJTUt9Us8cKB\n-----END PRIVATE KEY-----\n'
        ]));
    }

    protected function tearDown(): void
    {
        if (file_exists($_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'])) {
            unlink($_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH']);
        }
    }

    public function testGoogleSheetsServiceCanBeInstantiated(): void
    {
        $this->googleSheetsService = new GoogleSheetsService($this->logger);
        $this->assertInstanceOf(GoogleSheetsService::class, $this->googleSheetsService);
    }

    public function testGetSpreadsheetId(): void
    {
        $this->googleSheetsService = new GoogleSheetsService($this->logger);
        $spreadsheetId = $this->googleSheetsService->getSpreadsheetId();
        $this->assertEquals('test-spreadsheet-id', $spreadsheetId);
    }
}
