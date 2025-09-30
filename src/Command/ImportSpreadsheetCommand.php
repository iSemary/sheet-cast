<?php

namespace App\Command;

use App\DTO\ImportCommandOptions;
use App\Handler\ImportSpreadsheetHandler;
use App\Logger\Logger;
use App\Parser\XmlParser;
use App\Processor\SheetsDataProcessor;
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

    private ImportSpreadsheetHandler $handler;

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

        $logger = new Logger();
        $ftpService = new FtpService($logger);
        $xmlParser = new XmlParser($logger);
        $googleSheetsService = new GoogleSheetsService($logger);
        $sheetsDataProcessor = new SheetsDataProcessor($logger, $xmlParser, $googleSheetsService);

        $this->handler = new ImportSpreadsheetHandler($logger, $ftpService, $xmlParser, $sheetsDataProcessor);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sheet Cast - XML Data Import');

        $options = ImportCommandOptions::fromInput(
            $input->getOption('file') ?? $_ENV['FTP_FILE'] ?? 'coffee_feed.xml',
            (int) $input->getOption('limit'),
            $input->getOption('push-to-sheets')
        );

        $result = $this->handler->handle($options);

        if (!$result->success) {
            $io->error('Import failed: ' . $result->error);
            return Command::FAILURE;
        }

        if (empty($result->data)) {
            $io->warning('No data found in XML file');
            return Command::SUCCESS;
        }

        // Display results
        $this->displayResults($result, $io);

        return Command::SUCCESS;
    }

    private function displayResults($result, SymfonyStyle $io): void
    {
        $io->success('Import completed successfully');
        $io->info("Records processed: {$result->recordsProcessed}");
        $io->info("Records displayed: {$result->recordsDisplayed}");

        if ($result->pushedToSheets) {
            $io->success('Data successfully pushed to Google Sheets');
        }

        if ($result->recordsDisplayed > 0) {
            $displayData = array_slice($result->data, 0, $result->recordsDisplayed);
            $io->table(
                array_keys($displayData[0] ?? []),
                array_map('array_values', $displayData)
            );
        }
    }
}
