<?php

namespace App\Command;

use App\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSpreadsheetCommand extends Command
{
    protected static $defaultName = 'app:import-spreadsheet';
    protected static $defaultDescription = 'Import spreadsheet data';
    
    private Logger $logger;

    protected function configure(): void
    {
        $this->setName('app:import-spreadsheet')
            ->setDescription('Import spreadsheet data');
    }

    public function __construct()
    {
        parent::__construct();
        $this->logger = new Logger();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Starting spreadsheet import command');
        
        $output->writeln('Hello, World!');
        
        // Read environment variables
        $appName = $_ENV['APP_NAME'] ?? 'Unknown App';
        $appVersion = $_ENV['APP_VERSION'] ?? 'Unknown Version';
        $apiKey = $_ENV['GOOGLE_SHEETS_API_KEY'] ?? 'Not Set';
        $spreadsheetId = $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID'] ?? 'Not Set';
        $batchSize = $_ENV['IMPORT_BATCH_SIZE'] ?? '100';
        $debug = $_ENV['APP_DEBUG'] ?? 'false';
        
        $this->logger->debug('Environment configuration loaded', [
            'app_name' => $appName,
            'app_version' => $appVersion,
            'api_key_set' => $apiKey !== 'Not Set',
            'spreadsheet_id_set' => $spreadsheetId !== 'Not Set',
            'batch_size' => $batchSize,
            'debug_mode' => $debug
        ]);
        
        $output->writeln('');
        $output->writeln('Environment Configuration:');
        $output->writeln('App Name: ' . $appName);
        $output->writeln('App Version: ' . $appVersion);
        $output->writeln('API Key: ' . (strlen($apiKey) > 10 ? substr($apiKey, 0, 10) . '...' : $apiKey));
        $output->writeln('Spreadsheet ID: ' . (strlen($spreadsheetId) > 10 ? substr($spreadsheetId, 0, 10) . '...' : $spreadsheetId));
        $output->writeln('Batch Size: ' . $batchSize);
        $output->writeln('Debug Mode: ' . $debug);
        
        $this->logger->info('Spreadsheet import command completed successfully');
        
        return Command::SUCCESS;
    }
}
