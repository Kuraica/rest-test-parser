# Laravel Application for Fetching and Transforming API Data

## Project Overview
This Laravel application is designed to fetch data from the external API endpoint [https://rest-test-eight.vercel.app/api/test](https://rest-test-eight.vercel.app/api/test), transform the data into a specified structure, and store the transformed data into a database. The application provides three endpoints to access the data:

1. `/api/files-and-directories` - Parses data from the external API and returns a structured JSON response with all directories and files.
2. `/api/directories` - Lists only directories in a paginated format.
3. `/api/files` - Lists only files in a paginated format.

The data structure transformation is based on the URLs provided by the external API, extracting IP addresses, directories, subdirectories, and files. The transformed data is stored in the database and cached for quick access.

## Endpoints
### `/api/files-and-directories`
Parses data from the external API endpoint and returns the following structure:
```json
{
  "<IP address>": [
    {
      "<directory name>": [
        {
          "<sub-directory name>": [
            "<file name>",
            "<file name>",
            "<file name>"
          ]
        },
        {
          "<sub-directory name>": [
            "<file name>",
            "<file name>",
            "<file name>"
          ]
        },
        "<file name>",
        "<file name>",
        "<file name>"
      ]
    },
    {
      "<directory name>": [
        "<file name>",
        "<file name>",
        "<file name>"
      ]
    }
  ]
}
```

### `/api/directories`
Returns a paginated list of directories with a limit of 100 records per page.

### `/api/files`
Returns a paginated list of files with a limit of 100 records per page.

## Implementation Notes
The application addresses the problem of large dataset load times and delays from the external API by utilizing a queue system to fetch and process data in the background. The processed data is cached in Redis to provide quick responses to API requests.

## Getting Started

### Prerequisites
- Docker
- Docker Compose

## Installation

Clone the repository:

```bash
git clone https://github.com/Kuraica/rest-test-parser.git
cd rest-test-parser
```

Build and start the Docker containers:

```bash
docker-compose up --build -d
```

Access the application container:

```bash
docker-compose exec --user root app bash
```

Navigate to the application directory and install dependencies:

```bash
cd /var/www
composer install
```
Run tests to ensure everything is set up correctly:

```bash
php artisan test
```

Process the API data by dispatching a job to the queue:
```bash
php artisan process:apidata https://rest-test-eight.vercel.app/api/test
```

Start the queue worker:
```bash
php artisan queue:work --timeout=900 --memory=1024
```

Feel free to adjust the repository URL and any other details specific to your project setup.
