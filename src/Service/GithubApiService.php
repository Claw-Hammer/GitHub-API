<?php

namespace App\Service;

use App\Entity\GithubPhpProject;
use App\Repository\GithubPhpProjectRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubApiService
{
    private const API_URL = 'https://api.github.com/search/repositories';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GithubPhpProjectRepository $repository,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchTopPhpProjects(int $limit = 30): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => [
                'q' => 'language:php',
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => $limit,
            ],
        ]);

        $data = $response->toArray();

        return $data['items'] ?? [];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function syncProjects(): int
    {
        $items = $this->fetchTopPhpProjects();
        $count = 0;

        foreach ($items as $item) {
            $existing = $this->repository->findOneBy(['repositoryId' => $item['id']]);

            if ($existing === null) {
                $project = new GithubPhpProject();
                $project->setRepositoryId($item['id']);
            } else {
                $project = $existing;
            }

            $project->setName($item['full_name']);
            $project->setUrl($item['html_url']);
            $project->setCreatedDate(new DateTimeImmutable($item['created_at']));
            $project->setLastPushDate(isset($item['pushed_at']) ? new DateTimeImmutable($item['pushed_at']) : null);
            $project->setDescription($item['description'] ?? null);
            $project->setStars($item['stargazers_count']);

            $this->repository->save($project, true);
            ++$count;
        }

        return $count;
    }
}
