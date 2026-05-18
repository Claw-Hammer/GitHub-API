<?php

namespace App\Tests\Controller;

use App\Entity\GithubPhpProject;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GithubPhpProjectControllerTest extends WebTestCase
{
    public function testIndexRedirectsToGithubProjects(): void
    {
        $client = static::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/');

        self::assertResponseRedirects('/github-projects', 302);
    }

    public function testIndexPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/github-projects');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Most Starred PHP Projects');
    }

    public function testIndexShowsProjects(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $uniqueId = random_int(100000, 999999);
        $project = new GithubPhpProject();
        $project->setRepositoryId($uniqueId);
        $project->setName('test/project-' . $uniqueId);
        $project->setUrl('https://github.com/test/project-' . $uniqueId);
        $project->setCreatedDate(new DateTimeImmutable());
        $project->setLastPushDate(new DateTimeImmutable());
        $project->setDescription('Test project');
        $project->setStars(999999999);

        $entityManager->persist($project);
        $entityManager->flush();

        $crawler = $client->request('GET', '/github-projects');

        self::assertResponseIsSuccessful();
        $projectNames = $crawler->filter('.project-name')->each(fn($node) => $node->text());
        self::assertContains('test/project-' . $uniqueId, $projectNames);

        $entityManager->remove($project);
        $entityManager->flush();
    }

    public function testDetailPageShowsProjectInfo(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $uniqueId = random_int(100000, 999999);
        $project = new GithubPhpProject();
        $project->setRepositoryId($uniqueId);
        $project->setName('test/detail-project-' . $uniqueId);
        $project->setUrl('https://github.com/test/detail-project-' . $uniqueId);
        $project->setCreatedDate(new DateTimeImmutable('2023-01-01'));
        $project->setLastPushDate(new DateTimeImmutable('2024-06-15'));
        $project->setDescription('A test project for detail view');
        $project->setStars(10000);

        $entityManager->persist($project);
        $entityManager->flush();

        $client->request('GET', '/github-projects/' . $project->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'test/detail-project-' . $uniqueId);
        self::assertSelectorTextContains('.detail-description', 'A test project for detail view');
        self::assertSelectorTextContains('.detail-metric-value.stars', '10,000');

        $entityManager->remove($project);
        $entityManager->flush();
    }

    public function testDetailPageReturns404ForNonExistentProject(): void
    {
        $client = static::createClient();
        $client->request('GET', '/github-projects/99999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testRefreshRequiresPostMethod(): void
    {
        $client = static::createClient();
        $client->request('GET', '/github-projects/refresh');

        self::assertResponseStatusCodeSame(405);
    }

    public function testRefreshUpdatesProjects(): void
    {
        $client = static::createClient();
        $client->request('POST', '/github-projects/refresh');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/vnd.turbo-stream.html; charset=UTF-8');
    }
}
