<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DirectoryRepositoryInterface;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class ApiDataController extends Controller
{
    public function __construct(
        protected DirectoryRepositoryInterface $directoryRepository,
        protected FileRepositoryInterface      $fileRepository,
        protected Connection                   $redisClient,
    ) {}

    /**
     * Get files and directories from Redis.
     *
     * @return JsonResponse
     */
    public function getFilesAndDirectories(): JsonResponse
    {
        $data = $this->redisClient->get('parsed_url_directories_and_files');

        if ($data) {
            return response()->json(json_decode($data, true));
        } else {
            return response()->json([
                                        'message' => 'Data processing is in progress. Please try again later.',
                                    ], 202);
        }
    }

    /**
     * Get paginated list of directories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDirectories(Request $request): JsonResponse
    {
        $page = $request->query('page', 1);
        $cacheKey = 'parsed_url_directories_page_' . $page;
        $data = $this->redisClient->get($cacheKey);

        if ($data) {
            return response()->json(json_decode($data, true));
        }

        $directories = $this->directoryRepository->paginate(100, $page);
        $directoriesArray = $directories->toArray(); // Convert to array

        $this->redisClient->set($cacheKey, json_encode($directoriesArray)); // Save as JSON

        return response()->json($directoriesArray);
    }

    /**
     * Get paginated list of files.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFiles(Request $request): JsonResponse
    {
        $page = $request->query('page', 1);
        $cacheKey = 'parsed_url_files_page_' . $page;
        $data = $this->redisClient->get($cacheKey);

        if ($data) {
            return response()->json(json_decode($data, true));
        }

        $files = $this->fileRepository->paginate(100, $page);
        $filesArray = $files->toArray(); // Convert to array

        $this->redisClient->set($cacheKey, json_encode($filesArray)); // Save as JSON

        return response()->json($filesArray);
    }
}
