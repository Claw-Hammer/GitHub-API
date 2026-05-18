<?php

namespace App\Tests\Entity;

use App\Entity\GithubPhpProject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class GithubPhpProjectTest extends TestCase
{
    public function testInitialState(): void
    {
        $project = new GithubPhpProject();

        $this->assertNull($project->getId());
        $this->assertNull($project->getRepositoryId());
        $this->assertNull($project->getName());
        $this->assertNull($project->getUrl());
        $this->assertNull($project->getCreatedDate());
        $this->assertNull($project->getLastPushDate());
        $this->assertNull($project->getDescription());
        $this->assertNull($project->getStars());
    }

    public function testSetAndGetRepositoryId(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setRepositoryId(12345);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame(12345, $project->getRepositoryId());
    }

    public function testSetAndGetName(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setName('symfony/symfony');

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame('symfony/symfony', $project->getName());
    }

    public function testSetAndGetUrl(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setUrl('https://github.com/symfony/symfony');

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame('https://github.com/symfony/symfony', $project->getUrl());
    }

    public function testSetAndGetCreatedDate(): void
    {
        $project = new GithubPhpProject();
        $date = new DateTimeImmutable('2020-01-01');
        $result = $project->setCreatedDate($date);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame($date, $project->getCreatedDate());
    }

    public function testSetAndGetLastPushDate(): void
    {
        $project = new GithubPhpProject();
        $date = new DateTimeImmutable('2024-06-15');
        $result = $project->setLastPushDate($date);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame($date, $project->getLastPushDate());
    }

    public function testSetLastPushDateToNull(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setLastPushDate(null);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertNull($project->getLastPushDate());
    }

    public function testSetAndGetDescription(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setDescription('A PHP framework');

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame('A PHP framework', $project->getDescription());
    }

    public function testSetDescriptionToNull(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setDescription(null);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertNull($project->getDescription());
    }

    public function testSetAndGetStars(): void
    {
        $project = new GithubPhpProject();
        $result = $project->setStars(100000);

        $this->assertInstanceOf(GithubPhpProject::class, $result);
        $this->assertSame(100000, $project->getStars());
    }

    public function testFluentInterface(): void
    {
        $date = new DateTimeImmutable('2024-01-01');
        $project = (new GithubPhpProject())
            ->setRepositoryId(999)
            ->setName('laravel/laravel')
            ->setUrl('https://github.com/laravel/laravel')
            ->setCreatedDate($date)
            ->setLastPushDate($date)
            ->setDescription('PHP framework')
            ->setStars(75000);

        $this->assertSame(999, $project->getRepositoryId());
        $this->assertSame('laravel/laravel', $project->getName());
        $this->assertSame('https://github.com/laravel/laravel', $project->getUrl());
        $this->assertSame($date, $project->getCreatedDate());
        $this->assertSame($date, $project->getLastPushDate());
        $this->assertSame('PHP framework', $project->getDescription());
        $this->assertSame(75000, $project->getStars());
    }
}
