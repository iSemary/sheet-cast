<?php

namespace App\Service;

use App\Logger\Logger;

class FtpService
{
    private Logger $logger;
    private string $host;
    private string $username;
    private string $password;
    private ?\FTP\Connection $connection = null;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->host = $_ENV['FTP_HOST'] ?? '';
        $this->username = $_ENV['FTP_USERNAME'] ?? '';
        $this->password = $_ENV['FTP_PASSWORD'] ?? '';
    }

    public function connect(): bool
    {
        try {
            $this->logger->info('Connecting to FTP server', ['host' => $this->host]);
            
            $timeout = 30;
            $this->connection = ftp_connect($this->host, 21, $timeout);
            
            if (!$this->connection) {
                throw new \Exception('Failed to connect to FTP server');
            }

            $login = ftp_login($this->connection, $this->username, $this->password);
            
            if (!$login) {
                throw new \Exception('Failed to login to FTP server');
            }

            ftp_pasv($this->connection, true);
            
            ftp_set_option($this->connection, FTP_USEPASVADDRESS, false);
            
            $this->logger->info('Successfully connected to FTP server');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('FTP connection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }


    public function getFileContent(string $remoteFile): ?string
    {
        if (!$this->connection) {
            $this->logger->error('FTP connection not established');
            return null;
        }

        try {
            $this->logger->info('Reading file content from FTP', ['remote_file' => $remoteFile]);

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'ftp_');
            
            // Set timeout for the operation
            $timeout = 300; // 5 minutes
            $oldTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $timeout);
            
            // Try to get the file
            $result = @ftp_get($this->connection, $tempFile, $remoteFile, FTP_BINARY);
            
            // Restore original timeout
            ini_set('default_socket_timeout', $oldTimeout);
            
            if ($result && file_exists($tempFile)) {
                $content = file_get_contents($tempFile);
                unlink($tempFile);
                
                if ($content !== false) {
                    $this->logger->info('File content read successfully', [
                        'remote_file' => $remoteFile,
                        'size' => strlen($content)
                    ]);
                    
                    return $content;
                } else {
                    throw new \Exception('Failed to read downloaded file content');
                }
            } else {
                // Clean up temp file if it exists
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                throw new \Exception('Failed to download file from FTP server');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to read file content', [
                'remote_file' => $remoteFile,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            ftp_close($this->connection);
            $this->connection = null;
            $this->logger->info('FTP connection closed');
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

}
