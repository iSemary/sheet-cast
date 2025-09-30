<?php

return [
    'level' => $_ENV['LOG_LEVEL'] ?? 'info',
    'file' => $_ENV['LOG_FILE'] ?? 'logs/app.log',
    'max_files' => (int) ($_ENV['LOG_MAX_FILES'] ?? 5),
];
