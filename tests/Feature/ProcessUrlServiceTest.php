<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Services\ProcessUrlService;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Redis\Connections\Connection;
use Psr\Log\LoggerInterface;

class ProcessUrlServiceTest extends TestCase
{
    public function test_process_url_service()
    {
        // Mock data
        $apiResponse = [
            'items' => [
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/ADV-H5-New/README.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/ADV-H5-New/VisualSVN.lck'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/ADV-H5-New/hooks-env.tmpl'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/AT-APP/README.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/AT-APP/VisualSVN.lck'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/AT-APP/hooks-env.tmpl'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/README.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/VisualSVN.lck'],
                ['fileUrl' => 'http://34.8.32.234:48183/SvnRep/hooks-env.tmpl'],
                ['fileUrl' => 'http://34.8.32.234:48183/www/README.txt'],
                ['fileUrl' => 'http://34.8.32.234:48183/www/VisualSVN.lck'],
                ['fileUrl' => 'http://34.8.32.234:48183/www/hooks-env.tmpl'],
            ]
        ];

        $expectedResult = [
            "34.8.32.234" => [
                "SvnRep" => [
                    "ADV-H5-New" => [
                        "README.txt",
                        "VisualSVN.lck",
                        "hooks-env.tmpl"
                    ],
                    "AT-APP" => [
                        "README.txt",
                        "VisualSVN.lck",
                        "hooks-env.tmpl"
                    ],
                    "README.txt",
                    "VisualSVN.lck",
                    "hooks-env.tmpl"
                ],
                "www" => [
                    "README.txt",
                    "VisualSVN.lck",
                    "hooks-env.tmpl"
                ]
            ]
        ];

        // Mock dependencies
        $directoryRepoMock = Mockery::mock(DirectoryRepositoryInterface::class);
        $fileRepoMock = Mockery::mock(FileRepositoryInterface::class);
        $httpClientMock = Mockery::mock(HttpClientFactory::class);
        $redisMock = Mockery::mock(Connection::class);
        $loggerMock = Mockery::mock(LoggerInterface::class);

        // Set up HTTP client mock to return our mocked API response
        $httpResponseMock = Mockery::mock(Response::class);
        $httpResponseMock->shouldReceive('successful')
            ->andReturn(true);
        $httpResponseMock->shouldReceive('json')
            ->andReturn($apiResponse);

        $httpClientMock->shouldReceive('get')
            ->with('https://rest-test-eight.vercel.app/api/test')
            ->andReturn($httpResponseMock);

        // Set up Redis mock
        $redisMock->shouldReceive('set')
            ->once()
            ->with('parsed_url_directories_and_files', json_encode($expectedResult));

        $redisMock->shouldReceive('keys')
            ->once()
            ->with('parsed_url*')
            ->andReturn([]);

        // Mock method calls on repositories
        $directoryRepoMock->shouldReceive('truncate')->once();
        $fileRepoMock->shouldReceive('truncate')->once();
        $directoryRepoMock->shouldReceive('firstOrCreate')->andReturnUsing(function ($data) {
            $directory = new \stdClass();
            $directory->id = 1;
            $directory->name = $data['name'];
            $directory->parent_id = $data['parent_id'];
            $directory->path = $data['path'];
            return $directory;
        });
        $fileRepoMock->shouldReceive('create')->andReturnUsing(function ($data) {
            $file = new \stdClass();
            $file->name = $data['name'];
            $file->directory_id = $data['directory_id'];
            $file->path = $data['path'];
            return $file;
        });

        // Set up logger mock to expect info calls
        $loggerMock->shouldReceive('info')
            ->times(4)
            ->with(Mockery::type('string'));

        // Create instance of the service
        $service = new ProcessUrlService(
            $directoryRepoMock,
            $fileRepoMock,
            $httpClientMock,
            $redisMock,
            $loggerMock,
            'https://rest-test-eight.vercel.app/api/test'
        );

        // Call the method we are testing
        $service->fetchDataAndSave();

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
