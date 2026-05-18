<?php

namespace App\Tests\Service;

use App\Entity\GithubPhpProject;
use App\Repository\GithubPhpProjectRepository;
use App\Service\GithubApiService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AllowMockObjectsWithoutExpectations]
class GithubApiServiceTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private GithubPhpProjectRepository&MockObject $repository;
    private GithubApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->repository = $this->createMock(GithubPhpProjectRepository::class);
        $this->service = new GithubApiService($this->httpClient, $this->repository);
    }

    public function testFetchTopPhpProjectsReturnsItems(): void
    {
        $expectedItems = [
            ['full_name' => 'symfony/symfony', 'stargazers_count' => 30000],
            ['full_name' => 'laravel/laravel', 'stargazers_count' => 25000],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn(['items' => $expectedItems]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.github.com/search/repositories',
                [
                    'query' => [
                        'q' => 'language:php',
                        'sort' => 'stars',
                        'order' => 'desc',
                        'per_page' => 10,
                    ],
                ]
            )
            ->willReturn($response);

        $result = $this->service->fetchTopPhpProjects();

        $this->assertSame($expectedItems, $result);
    }

    public function testFetchTopPhpProjectsWithCustomLimit(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['items' => []]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.github.com/search/repositories',
                [
                    'query' => [
                        'q' => 'language:php',
                        'sort' => 'stars',
                        'order' => 'desc',
                        'per_page' => 25,
                    ],
                ]
            )
            ->willReturn($response);

        $this->service->fetchTopPhpProjects(25);
    }

    public function testFetchTopPhpProjectsReturnsEmptyWhenNoItems(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([]);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->service->fetchTopPhpProjects();

        $this->assertSame([], $result);
    }

    public function testSyncProjectsCreatesNewProjects(): void
    {
        $apiItems = [
            [
                'id' => 1,
                'full_name' => 'symfony/symfony',
                'html_url' => 'https://github.com/symfony/symfony',
                'created_at' => '2020-01-01T00:00:00Z',
                'pushed_at' => '2024-06-15T12:00:00Z',
                'description' => 'Symfony framework',
                'stargazers_count' => 30000,
            ],
            [
                'id' => 2,
                'full_name' => 'laravel/laravel',
                'html_url' => 'https://github.com/laravel/laravel',
                'created_at' => '2019-06-01T00:00:00Z',
                'pushed_at' => null,
                'description' => null,
                'stargazers_count' => 25000,
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['items' => $apiItems]);

        $this->httpClient->method('request')->willReturn($response);
        $this->repository->method('findOneBy')->willReturn(null);

        $savedProjects = [];
        $this->repository->method('save')
            ->willReturnCallback(function (GithubPhpProject $project) use (&$savedProjects): void {
                $savedProjects[] = $project;
            });

        $count = $this->service->syncProjects();

        $this->assertSame(2, $count);
        $this->assertCount(2, $savedProjects);

        $this->assertSame('symfony/symfony', $savedProjects[0]->getName());
        $this->assertSame(30000, $savedProjects[0]->getStars());
        $this->assertSame('Symfony framework', $savedProjects[0]->getDescription());

        $this->assertNull($savedProjects[1]->getDescription());
        $this->assertNull($savedProjects[1]->getLastPushDate());
    }

    public function testSyncProjectsUpdatesExistingProjects(): void
    {
        $existingProject = new GithubPhpProject();
        $existingProject->setRepositoryId(1);
        $existingProject->setName('symfony/symfony-old');
        $existingProject->setStars(20000);

        $apiItems = [
            [
                'id' => 1,
                'full_name' => 'symfony/symfony',
                'html_url' => 'https://github.com/symfony/symfony',
                'created_at' => '2020-01-01T00:00:00Z',
                'pushed_at' => '2024-06-15T12:00:00Z',
                'description' => 'Updated description',
                'stargazers_count' => 35000,
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['items' => $apiItems]);

        $this->httpClient->method('request')->willReturn($response);
        $this->repository->method('findOneBy')->willReturn($existingProject);

        $savedProjects = [];
        $this->repository->method('save')
            ->willReturnCallback(function (GithubPhpProject $project) use (&$savedProjects): void {
                $savedProjects[] = $project;
            });

        $count = $this->service->syncProjects();

        $this->assertSame(1, $count);
        $this->assertSame($existingProject, $savedProjects[0]);
        $this->assertSame(35000, $existingProject->getStars());
        $this->assertSame('Updated description', $existingProject->getDescription());
    }
}
