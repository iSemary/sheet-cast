<?php

namespace Tests\Unit;

use App\Command\ImportSpreadsheetCommand;
use PHPUnit\Framework\TestCase;

class ImportSpreadsheetCommandTest extends TestCase
{
    protected function setUp(): void
    {
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

    public function testCommandName(): void
    {
        $command = new ImportSpreadsheetCommand();
        $this->assertEquals('app:import-spreadsheet', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = new ImportSpreadsheetCommand();
        $this->assertEquals('Import spreadsheet data from XML file', $command->getDescription());
    }

    public function testCommandHasOptions(): void
    {
        $command = new ImportSpreadsheetCommand();
        $definition = $command->getDefinition();
        
        $this->assertTrue($definition->hasOption('file'));
        $this->assertTrue($definition->hasOption('limit'));
        $this->assertTrue($definition->hasOption('push-to-sheets'));
    }

    public function testCommandOptionsHaveCorrectTypes(): void
    {
        $command = new ImportSpreadsheetCommand();
        $definition = $command->getDefinition();
        
        $fileOption = $definition->getOption('file');
        $this->assertFalse($fileOption->isValueRequired());
        
        $limitOption = $definition->getOption('limit');
        $this->assertFalse($limitOption->isValueRequired());
        
        $pushOption = $definition->getOption('push-to-sheets');
        $this->assertFalse($pushOption->acceptValue());
    }
}
