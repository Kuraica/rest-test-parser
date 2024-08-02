<?php

namespace Tests\Feature;

use App\Jobs\ProcessApiData;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchProcessApiDataTest extends TestCase
{
    public function test_dispatches_job_with_url()
    {
        Queue::fake();

        $url = 'https://rest-test-eight.vercel.app/api/test';

        $this->artisan('process:apidata', ['url' => $url])
            ->expectsOutput('Job dispatched to process API data.')
            ->assertExitCode(0);

        Queue::assertPushed(ProcessApiData::class, function ($job) use ($url) {
            return $job->apiUrl === $url;
        });
    }
}
