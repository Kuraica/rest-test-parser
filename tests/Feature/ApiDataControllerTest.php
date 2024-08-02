<?php

namespace Tests\Feature;

use App\Models\Directory;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;
use Illuminate\Redis\Connections\Connection;

class ApiDataControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_files_and_directories()
    {
        $redisMock = Mockery::mock(Connection::class);
        $redisMock->shouldReceive('get')
            ->with('parsed_url_directories_and_files')
            ->andReturn(json_encode(['data' => 'test']));

        $this->app->instance(Connection::class, $redisMock);

        $response = $this->get('/api/files-and-directories');

        $response->assertStatus(200)
            ->assertJson(['data' => 'test']);
    }

    public function test_get_files_and_directories_processing()
    {
        $redisMock = Mockery::mock(Connection::class);
        $redisMock->shouldReceive('get')
            ->with('parsed_url_directories_and_files')
            ->andReturn(null);

        $this->app->instance(Connection::class, $redisMock);

        $response = $this->get('/api/files-and-directories');

        $response->assertStatus(202)
            ->assertJson(['message' => 'Data processing is in progress. Please try again later.']);
    }

    public function test_get_directories()
    {
        $page = 1;
        $cacheKey = 'parsed_url_directories_page_' . $page;

        $redisMock = Mockery::mock(Connection::class);
        $redisMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn(null);

        $redisMock->shouldReceive('set')->with($cacheKey, Mockery::type('string'));

        $this->app->instance(Connection::class, $redisMock);

        Directory::factory()->count(150)->create();

        $response = $this->get('/api/directories?page=1');

        $response->assertStatus(200)
            ->assertJsonCount(100, 'data')
            ->assertJsonStructure([
                                      'current_page',
                                      'data'  => [
                                          '*' => ['id', 'name', 'parent_id', 'path', 'created_at', 'updated_at'],
                                      ],
                                      'first_page_url',
                                      'from',
                                      'last_page',
                                      'last_page_url',
                                      'links' => [
                                          '*' => ['url', 'label', 'active'],
                                      ],
                                      'next_page_url',
                                      'path',
                                      'per_page',
                                      'prev_page_url',
                                      'to',
                                      'total',
                                  ]);
    }

    public function test_get_files()
    {
        $page = 1;
        $cacheKey = 'parsed_url_files_page_' . $page;

        $redisMock = Mockery::mock(Connection::class);
        $redisMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn(null);

        $redisMock->shouldReceive('set')->with($cacheKey, Mockery::type('string'));

        $this->app->instance(Connection::class, $redisMock);

        File::factory()->count(150)->create();

        $response = $this->get('/api/files?page=1');

        $response->assertStatus(200)
            ->assertJsonCount(100, 'data')
            ->assertJsonStructure([
                                      'current_page',
                                      'data'  => [
                                          '*' => ['id', 'name', 'directory_id', 'path', 'created_at', 'updated_at'],
                                      ],
                                      'first_page_url',
                                      'from',
                                      'last_page',
                                      'last_page_url',
                                      'links' => [
                                          '*' => ['url', 'label', 'active'],
                                      ],
                                      'next_page_url',
                                      'path',
                                      'per_page',
                                      'prev_page_url',
                                      'to',
                                      'total',
                                  ]);
    }
}
