<?php

namespace App\Command;

use App\Logger\Logger;
use App\Parser\XmlParser;
use App\Service\FtpService;
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

    protected function configure(): void
    {
        $this->setName('app:import-spreadsheet')
            ->setDescription('Import spreadsheet data from XML file')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'XML file path (local or remote)', null)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of records to display', 10);
    }

    public function __construct()
    {
        parent::__construct();
        $this->logger = new Logger();
        $this->ftpService = new FtpService($this->logger);
        $this->xmlParser = new XmlParser($this->logger);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->logger->info('Starting spreadsheet import command');
        
        $filePath = $input->getOption('file') ?? $_ENV['FTP_FILE'] ?? 'coffee_feed.xml';
        $limit = (int) $input->getOption('limit');
        
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
            
            // Display data in table
            $this->displayDataTable($data, $limit, $io);
            
            $this->logger->info('Spreadsheet import command completed successfully', [
                'records_processed' => count($data),
                'records_displayed' => min($limit, count($data))
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

    private function displayDataTable(array $data, int $limit, SymfonyStyle $io): void
    {
        $displayData = array_slice($data, 0, $limit);
        
        if (empty($displayData)) {
            $io->warning('No data to display');
            return;
        }
        
        $headers = $this->xmlParser->getTableHeaders($displayData);
        
        $tableData = [];
        foreach ($displayData as $index => $row) {
            $tableRow = ['#' => $index + 1];
            foreach ($headers as $header) {
                $tableRow[$header] = $row[$header] ?? '';
            }
            $tableData[] = $tableRow;
        }
        
        $io->section('XML Data (' . count($displayData) . ' of ' . count($data) . ' records)');
        $io->table(array_merge(['#'], $headers), $tableData);
        
        if (count($data) > $limit) {
            $io->note("Showing first {$limit} records. Use --limit option to show more.");
        }
    }
}
