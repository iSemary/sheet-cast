# Sheet Cast

**Sheet Cast**

## About

Sheet Cast is a simple command line tool that reads XML files and sends the data to Google Sheets. It works with local files or files from FTP servers.

## Features

- **XML Processing**: Parse XML files with dynamic field extraction
- **FTP Integration**: Fetch XML files from remote FTP servers
- **Google Sheets API**: Push data directly to Google Spreadsheets
- **Comprehensive Logging**: Detailed logging with Monolog
- **Error Handling**: Robust error handling and recovery
- **Unit Testing**: Complete test coverage with PHPUnit

## Technologies

- **PHP 8.4**
- **Symfony Console**
- **PHPUnit**

## Packages Used
- **Google Sheets API**
- **Monolog**
- **vlucas/phpdotenv**

## Get Started

### Installation

#### Option 1: Install via Composer (Recommended)

```bash
# Install globally
composer global require isemary/sheet-cast

# Or install in your project
composer require isemary/sheet-cast
```

#### Option 2: Clone from GitHub

```bash
# Clone the repository
git clone https://github.com/iSemary/sheet-cast.git
cd sheet-cast

# Install dependencies
composer install

# Copy environment configuration
cp .env.example .env
```

### Quick Start

After installation, you can use the command directly:

```bash
# If installed globally
sheet-cast app:import-spreadsheet --file=your-file.xml --limit=10

# If installed in project
./vendor/bin/sheet-cast app:import-spreadsheet --file=your-file.xml --limit=10
```

### Environment Configuration

Create a `.env` file with your configuration:

```env
# Application Settings
APP_NAME="Sheet Cast"
APP_VERSION="1.0.0"
APP_DEBUG=true

# Google Sheets API Configuration
GOOGLE_SHEETS_SPREADSHEET_ID=your_spreadsheet_id_here
GOOGLE_SHEETS_CREDENTIALS_PATH=config/google-credentials.json

# Logging
LOG_LEVEL=debug
LOG_FILE=logs/app.log

# FTP Configuration
FTP_HOST=your.ftp.server.com
FTP_USERNAME=your_username
FTP_PASSWORD=your_password
FTP_FILE=your_file.xml
```

### Google Sheets Setup

1. **Create a Google Cloud Project**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project
   - Enable Google Sheets API

2. **Create Service Account**
   - Go to "APIs & Services" > "Credentials"
   - Create a Service Account
   - Download the JSON credentials file
   - Place it in `config/google-credentials.json`

3. **Share Your Spreadsheet**
   - Open your Google Spreadsheet
   - Click "Share"
   - Add the service account email (from the JSON file)
   - Grant "Editor" permissions

### Basic Usage

```bash
# Process FTP XML file + Push to Google Sheets
sheet-cast app:import-spreadsheet --file=coffee_feed.xml --push-to-sheets --limit=100

# Process local XML file + Push to Google Sheets
sheet-cast app:import-spreadsheet --file=test_data.xml --push-to-sheets --limit=3

# Display data without pushing to sheets
sheet-cast app:import-spreadsheet --file=test_data.xml --limit=10
```

## Testing

### Run Tests

```bash
# Run all tests
./vendor/bin/phpunit
```

## Docker

### Build and Run

```bash
# Build the Docker image
docker build -t sheet-cast .
```


### Logging Configuration

```php
// Custom log levels
LOG_LEVEL=debug

// Custom log file
LOG_FILE=logs/custom.log
```
