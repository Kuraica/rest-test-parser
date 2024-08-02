<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessApiData;

class DispatchProcessApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:apidata {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to process API data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
        ProcessApiData::dispatch($url);

        $this->info('Job dispatched to process API data.');
        return 0;
    }
}
