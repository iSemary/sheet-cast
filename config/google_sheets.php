<?php

return [
    'spreadsheet_id' => $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID'] ?? null,
    'credentials_path' => $_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'] ?? 'config/google-credentials.json',
    'application_name' => $_ENV['GOOGLE_SHEETS_APPLICATION_NAME'] ?? 'Sheet Cast',
];
