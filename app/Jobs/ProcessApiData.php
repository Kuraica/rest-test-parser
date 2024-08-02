<?php

namespace App\Jobs;

use App\Services\ProcessUrlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApiData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $apiUrl) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $processUrlService = app(ProcessUrlService::class, ['apiUrl' => $this->apiUrl]);
        $processUrlService->fetchDataAndSave();
    }
}
