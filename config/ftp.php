<?php

return [
    'host' => $_ENV['FTP_HOST'] ?? 'localhost',
    'port' => (int) ($_ENV['FTP_PORT'] ?? 21),
    'username' => $_ENV['FTP_USERNAME'] ?? null,
    'password' => $_ENV['FTP_PASSWORD'] ?? null,
    'timeout' => (int) ($_ENV['FTP_TIMEOUT'] ?? 90),
    'file' => $_ENV['FTP_FILE'] ?? 'coffee_feed.xml',
];
