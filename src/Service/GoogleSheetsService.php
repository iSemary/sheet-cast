<?php

namespace App\Service;

use App\Logger\Logger;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheetsService
{
    private Logger $logger;
    private Client $client;
    private Sheets $service;
    private string $spreadsheetId;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->spreadsheetId = $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID'] ?? '';
        
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Sheet Cast');
            $this->client->setScopes([Sheets::SPREADSHEETS]);
            $this->client->setAuthConfig($_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'] ?? '');
            $this->client->setAccessType('offline');

            $this->service = new Sheets($this->client);
            
            $this->logger->info('Google Sheets client initialized successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize Google Sheets client', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function writeData(array $data, string $range = 'A1'): array
    {
        try {
            $this->logger->info('Writing data to Google Sheets', [
                'spreadsheet_id' => $this->spreadsheetId,
                'range' => $range,
                'records_count' => count($data)
            ]);

            $body = new ValueRange([
                'values' => $data
            ]);

            $params = [
                'valueInputOption' => 'RAW'
            ];

            $result = $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            $this->logger->info('Google Sheets API response received', [
                'updated_cells' => $result->getUpdatedCells(),
                'updated_rows' => $result->getUpdatedRows(),
                'updated_columns' => $result->getUpdatedColumns(),
            ]);

            $this->logger->info('Data written successfully', [
                'updated_cells' => $result->getUpdatedCells(),
                'updated_rows' => $result->getUpdatedRows()
            ]);

            return [
                'success' => true,
                'updated_cells' => $result->getUpdatedCells(),
                'updated_rows' => $result->getUpdatedRows(),
                'updated_columns' => $result->getUpdatedColumns(),
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to write data to Google Sheets', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public function getSpreadsheetId(): string
    {
        return $this->spreadsheetId;
    }
}
