<?php

namespace App\Http\Controllers;

use App\Services\ProcessUrlService;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected $processUrlService;

    public function __construct(ProcessUrlService $processUrlService)
    {
        $this->processUrlService = $processUrlService;
    }

    /**
     * Fetch data from external API, process it, and save to database and Redis.
     *
     * @return JsonResponse
     */
    public function fetchData(): JsonResponse
    {
        $this->processUrlService->fetchDataAndSave();
        return response()->json(['message' => 'Data fetching and processing initiated']);
    }
}

