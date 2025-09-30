<?php

namespace App\Handler;

use App\DTO\ImportCommandOptions;
use App\DTO\ImportResult;
use App\Logger\Logger;
use App\Parser\XmlParser;
use App\Processor\SheetsDataProcessor;
use App\Service\FtpService;
use SimpleXMLElement;

class ImportSpreadsheetHandler
{
    public function __construct(
        private readonly Logger $logger,
        private readonly FtpService $ftpService,
        private readonly XmlParser $xmlParser,
        private readonly SheetsDataProcessor $sheetsDataProcessor
    ) {}

    public function handle(ImportCommandOptions $options): ImportResult
    {
        try {
            $this->logger->info('Starting spreadsheet import', [
                'file_path' => $options->filePath,
                'limit' => $options->limit,
                'push_to_sheets' => $options->pushToSheets
            ]);

            // Get XML content
            $xmlContent = $this->getXmlContent($options->filePath);
            if (!$xmlContent) {
                return ImportResult::failure('Failed to retrieve XML content');
            }

            // Parse XML
            $xml = $this->xmlParser->parseXml($xmlContent);
            if (!$xml) {
                return ImportResult::failure('Failed to parse XML content');
            }

            // Extract data
            $data = $this->xmlParser->extractData($xml);
            if (empty($data)) {
                $this->logger->info('No data found in XML file');
                return ImportResult::success($data, 0, 0, false);
            }

            // Push to Google Sheets if requested
            $pushedToSheets = false;
            if ($options->pushToSheets) {
                $result = $this->sheetsDataProcessor->processAndWriteData($data);
                $pushedToSheets = $result->success;
                
                if (!$result->success) {
                    $this->logger->error('Failed to push data to Google Sheets', [
                        'error' => $result->error
                    ]);
                }
            }

            $recordsDisplayed = min($options->limit, count($data));

            $this->logger->info('Spreadsheet import completed successfully', [
                'records_processed' => count($data),
                'records_displayed' => $recordsDisplayed,
                'pushed_to_sheets' => $pushedToSheets
            ]);

            return ImportResult::success(
                $data,
                count($data),
                $recordsDisplayed,
                $pushedToSheets
            );

        } catch (\Exception $e) {
            $this->logger->error('Import handler failed', ['error' => $e->getMessage()]);
            return ImportResult::failure($e->getMessage());
        }
    }

    private function getXmlContent(string $filePath): ?string
    {
        if (file_exists($filePath)) {
            $this->logger->info("Reading local file: {$filePath}");
            return file_get_contents($filePath);
        }

        $this->logger->info('Connecting to FTP server...');

        if (!$this->ftpService->connect()) {
            $this->logger->error('Failed to connect to FTP server');
            return null;
        }

        $this->logger->info('Connected to FTP server');

        $xmlContent = $this->ftpService->getFileContent($filePath);
        $this->ftpService->disconnect();

        return $xmlContent;
    }
}
