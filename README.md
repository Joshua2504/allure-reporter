# Allure Reporter

A simple web service for uploading and serving Allure test reports using Docker, PHP, and Nginx.

## Features

- Upload Allure report zip files via HTTP POST
- Automatic timestamp extraction from `data/timeline.json`
- Organized storage in `zip/` and `reports/` directories
- API key authentication for security
- Timezone support (Europe/Berlin)
- Docker-based deployment

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Clone or download the repository**

2. **Configure API Key**
   - Edit `.env` and set your secret API key:
     ```
     API_KEY=your_secure_api_key_here
     ```

3. **Start the services**
   ```bash
   docker-compose up -d
   ```

4. **Access the application**
   - Web interface: http://localhost
   - Upload endpoint: http://localhost/upload.php

## Configuration

### Environment Variables (.env)

- `API_KEY`: Secret key required for uploads

### Directory Structure

- `zip/{project}-{state}/`: Stores uploaded zip files
- `reports/{project}-{state}/`: Stores extracted reports

## API Usage

### Upload Report

Upload an Allure report zip file:

```bash
curl -X POST \
  -F "zip_file=@/path/to/allure-report.zip" \
  "http://localhost/upload.php?project=myproject&state=staging&api_key=your_api_key"
```

**Parameters:**
- `project` (required): Project name
- `state` (required): Environment/state (e.g., staging, production)
- `api_key` (required): Your API key from `.env`
- `zip_file` (required): The Allure report zip file

**Response:**
- Success: "Upload and extraction successful"
- Error: HTTP status code with error message

### Timestamp Extraction

The service automatically extracts the latest test completion timestamp from `data/timeline.json` in the zip file and uses it for naming directories and files.

## Accessing Reports

- **Index Page**: http://localhost - Links to reports and zip directories
- **Reports**: http://localhost/reports/ - Browse extracted Allure reports
- **Zips**: http://localhost/zip/ - Download original zip files

Reports are organized as: `reports/{project}-{state}/{YYYY-MM-DD_HH-MM-SS}/`

## Development

### Local Development

1. Install PHP and Nginx locally
2. Copy `.env` and set API_KEY
3. Run PHP built-in server or configure Nginx
4. Access via http://localhost:8000 (or your configured port)

### Docker Development

- Build custom image: `docker-compose build`
- View logs: `docker-compose logs`
- Stop services: `docker-compose down`

## Security

- API key authentication required for uploads
- Only specific directories are served by Nginx
- Sensitive files (`.env`) are gitignored

## Troubleshooting

- **Upload fails**: Check API key, file path, and zip contents
- **Invalid timestamp**: Ensure `data/timeline.json` exists in zip with valid "stop" times
- **Permission errors**: Check Docker volume permissions

## License

[Add license if applicable]