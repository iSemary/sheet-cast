<?php

namespace App\Command;

use App\Logger\Logger;
use App\Parser\XmlParser;
use App\Service\FtpService;
use App\Service\GoogleSheetsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportSpreadsheetCommand extends Command
{
    protected static $defaultName = 'app:import-spreadsheet';
    protected static $defaultDescription = 'Import spreadsheet data';

    private Logger $logger;
    private FtpService $ftpService;
    private XmlParser $xmlParser;
    private GoogleSheetsService $googleSheetsService;

    protected function configure(): void
    {
        $this->setName('app:import-spreadsheet')
            ->setDescription('Import spreadsheet data from XML file')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'XML file path (local or remote)', null)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of records to display', 10)
            ->addOption('push-to-sheets', 'p', InputOption::VALUE_NONE, 'Push data to Google Sheets');
    }

    public function __construct()
    {
        parent::__construct();
        $this->logger = new Logger();
        $this->ftpService = new FtpService($this->logger);
        $this->xmlParser = new XmlParser($this->logger);
        $this->googleSheetsService = new GoogleSheetsService($this->logger);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->logger->info('Starting spreadsheet import command');

        $filePath = $input->getOption('file') ?? $_ENV['FTP_FILE'] ?? 'coffee_feed.xml';
        $limit = (int) $input->getOption('limit');
        $pushToSheets = $input->getOption('push-to-sheets');

        $io->title('Sheet Cast - XML Data Import');

        try {
            // Get XML content
            $xmlContent = $this->getXmlContent($filePath, $io);

            if (!$xmlContent) {
                $io->error('Failed to retrieve XML content');
                return Command::FAILURE;
            }

            // Parse XML
            $xml = $this->xmlParser->parseXml($xmlContent);

            if (!$xml) {
                $io->error('Failed to parse XML content');
                return Command::FAILURE;
            }

            // Extract data
            $data = $this->xmlParser->extractData($xml);

            if (empty($data)) {
                $io->warning('No data found in XML file');
                return Command::SUCCESS;
            }

            // Push to Google Sheets if requested
            if ($pushToSheets) {
                $this->pushToGoogleSheets($data, $io);
            }

            $this->logger->info('Spreadsheet import command completed successfully', [
                'records_processed' => count($data),
                'records_displayed' => min($limit, count($data)),
                'pushed_to_sheets' => $pushToSheets
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Command execution failed', ['error' => $e->getMessage()]);
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getXmlContent(string $filePath, SymfonyStyle $io): ?string
    {
        if (file_exists($filePath)) {
            $io->info("Reading local file: {$filePath}");
            return file_get_contents($filePath);
        }

        $io->info('Connecting to FTP server...');

        if (!$this->ftpService->connect()) {
            $io->error('Failed to connect to FTP server');
            return null;
        }

        $io->success('Connected to FTP server');

        $xmlContent = $this->ftpService->getFileContent($filePath);
        $this->ftpService->disconnect();

        return $xmlContent;
    }

    private function pushToGoogleSheets(array $data, SymfonyStyle $io): void
    {
        try {
            $io->info('Pushing data to Google Sheets...');

            $headers = $this->xmlParser->getTableHeaders($data);
            $sheetData = [array_merge(['#'], $headers)];

            foreach ($data as $index => $row) {
                $sheetRow = [$index + 1];
                foreach ($headers as $header) {
                    $sheetRow[] = $row[$header] ?? '';
                }
                $sheetData[] = $sheetRow;
            }

            $result = $this->googleSheetsService->writeData($sheetData, 'A1');

            if ($result['success']) {
                $io->success('Data successfully pushed to Google Sheets');
                $io->info('Spreadsheet ID: ' . $this->googleSheetsService->getSpreadsheetId());
                $io->info('Updated cells: ' . $result['updated_cells']);
                $io->info('Updated rows: ' . $result['updated_rows']);
                $io->info('Updated columns: ' . $result['updated_columns']);
            } else {
                $io->error('Failed to push data to Google Sheets: ' . $result['error']);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to push data to Google Sheets', ['error' => $e->getMessage()]);
            $io->error('Error pushing to Google Sheets: ' . $e->getMessage());
        }
    }
}
