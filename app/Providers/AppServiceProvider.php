<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use App\Repositories\Contracts\FileRepositoryInterface;
use App\Repositories\DirectoryRepository;
use App\Repositories\FileRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DirectoryRepositoryInterface::class, DirectoryRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
