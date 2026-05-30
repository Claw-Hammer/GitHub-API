<?php

namespace App\Service;

use App\Entity\GithubPhpProject;
use App\Repository\GithubPhpProjectRepository;
use DateTimeImmutable;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubApiService
{
    private const API_URL = 'https://api.github.com/search/repositories';
    private const CACHE_TTL = 60; // 1 minute

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GithubPhpProjectRepository $repository,
        private readonly CacheItemPoolInterface $cache,
        private readonly string $githubToken,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     */
    public function fetchTopPhpProjects(int $limit = 500): array //increasing limit to 500
    {
        $cacheKey = 'github_php_projects_limit_' . $limit;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $options = [
            'query' => [
                'q' => 'language:php',
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => $limit,
            ],
        ];

        if ($this->githubToken !== '') {
            $options['auth_bearer'] = $this->githubToken;
        }

        $response = $this->httpClient->request('GET', self::API_URL, $options);

        $data = $response->toArray();
        $items = $data['items'] ?? [];

        $cacheItem->set($items);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);

        return $items;
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
        $project = null;

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

            $this->repository->save($project);  // no flush here to avoid multiple commits to the DB
            ++$count;
        }

        if ($project !== null) {
            $this->repository->save($project, true); // single flush
        }

        return $count;
    }
}
