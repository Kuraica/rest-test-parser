<?php

namespace App\Services;

use App\Repositories\Contracts\DirectoryRepositoryInterface;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Http\Client\Factory;
use Illuminate\Redis\Connections\Connection;
use Psr\Log\LoggerInterface;

class ProcessUrlService
{
    public function __construct(
        protected DirectoryRepositoryInterface $directoryRepository,
        protected FileRepositoryInterface      $fileRepository,
        protected Factory                      $httpClient,
        protected Connection                   $redisClient,
        protected LoggerInterface              $logger,
        public string                          $apiUrl
    ) {}

    /**
     * Fetch data from external API and save to database and Redis
     *
     * @return void
     */
    public function fetchDataAndSave(): void
    {
        $startTime = microtime(true);
        $this->logger->info('Data processing for API ' . $this->apiUrl . ' started at: ' . now());

        // Clear previous data
        $this->clearPreviousData();

        $response = $this->httpClient->get($this->apiUrl);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['items'])) {
                $this->logger->info('Data fetched successfully');
                $this->processData($data['items']);
            } else {
                $this->logger->error('Something went wrong: ' . $response->body());
            }

        } else {
            $this->logger->error('Failed to fetch data: ' . $response->body());
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->logger->info('Data processing ended at: ' . now());
        $this->logger->info('Data processing duration: ' . $duration . ' seconds');
    }

    /**
     * Clear previous data from database and Redis
     *
     * @return void
     */
    private function clearPreviousData(): void
    {
        // Clear database tables
        $this->directoryRepository->truncate();
        $this->fileRepository->truncate();

        // Clear Redis cache
        $keys = $this->redisClient->keys('parsed_url*');
        foreach ($keys as $key) {
            // remove prefix
            $this->redisClient->del(str_replace('laravel_database_', '', $key));
        }
    }

    /**
     * Process data and store in database and Redis
     *
     * @param array $data
     * @return void
     */
    private function processData(array $data)
    {
        $processedData = [];

        foreach ($data as $url) {
            $parts = parse_url($url['fileUrl']);
            $pathParts = explode('/', ltrim($parts['path'], '/'));

            $ip = $parts['host'];
            $directoryName = $pathParts[0];
            $subDirectories = array_slice($pathParts, 1, -1);
            $fileName = end($pathParts);

            // Handle case where the last part is an empty string (indicating no file name)
            if ($fileName === '') {
                array_pop($pathParts);
                $fileName = null;
            }

            if (!isset($processedData[$ip])) {
                $processedData[$ip] = [];
            }

            $currentDirectory = &$processedData[$ip][$directoryName];

            foreach ($subDirectories as $subDirectory) {
                if (!isset($currentDirectory[$subDirectory])) {
                    $currentDirectory[$subDirectory] = [];
                }
                $currentDirectory = &$currentDirectory[$subDirectory];
            }

            if ($fileName) {
                $currentDirectory[] = $fileName;
            }

            // Save to database
            $directory = $this->directoryRepository->firstOrCreate([
                                                                       'name'      => $directoryName,
                                                                       'parent_id' => null,
                                                                       'path'      => '/' . $directoryName,
                                                                   ]);

            $parentDirectory = $directory;
            foreach ($subDirectories as $subDirectoryName) {
                $subDirectory = $this->directoryRepository->firstOrCreate([
                                                                              'name'      => $subDirectoryName,
                                                                              'parent_id' => $parentDirectory->id,
                                                                              'path'      => $parentDirectory->path . '/' . $subDirectoryName,
                                                                          ]);
                $parentDirectory = $subDirectory;
            }

            if ($fileName) {
                $this->fileRepository->create([
                                                  'name'         => $fileName,
                                                  'directory_id' => $parentDirectory->id,
                                                  'path'         => $parentDirectory->path . '/' . $fileName,
                                              ]);
            }
        }

        // Save to Redis
        $this->redisClient->set('parsed_url_directories_and_files', json_encode($processedData));
    }
}
