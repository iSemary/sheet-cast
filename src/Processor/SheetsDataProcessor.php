<?php

namespace App\Processor;

use App\DTO\SheetsWriteResult;
use App\Logger\Logger;
use App\Parser\XmlParser;
use App\Service\GoogleSheetsService;

class SheetsDataProcessor
{
    public function __construct(
        private readonly Logger $logger,
        private readonly XmlParser $xmlParser,
        private readonly GoogleSheetsService $googleSheetsService
    ) {}

    public function processAndWriteData(array $data): SheetsWriteResult
    {
        try {
            $this->logger->info('Processing data for Google Sheets', [
                'records_count' => count($data)
            ]);

            $sheetData = $this->prepareSheetData($data);
            $result = $this->googleSheetsService->writeData($sheetData, 'A1');

            if ($result['success']) {
                return SheetsWriteResult::success(
                    $result['updated_cells'],
                    $result['updated_rows'],
                    $result['updated_columns']
                );
            }

            return SheetsWriteResult::failure($result['error']);

        } catch (\Exception $e) {
            $this->logger->error('Failed to process data for Google Sheets', [
                'error' => $e->getMessage()
            ]);
            return SheetsWriteResult::failure($e->getMessage());
        }
    }

    private function prepareSheetData(array $data): array
    {
        $headers = $this->xmlParser->getTableHeaders($data);
        $sheetData = [array_merge(['#'], $headers)];

        foreach ($data as $index => $row) {
            $sheetRow = [$index + 1];
            foreach ($headers as $header) {
                $sheetRow[] = $row[$header] ?? '';
            }
            $sheetData[] = $sheetRow;
        }

        return $sheetData;
    }

    public function getSpreadsheetId(): string
    {
        return $this->googleSheetsService->getSpreadsheetId();
    }
}
