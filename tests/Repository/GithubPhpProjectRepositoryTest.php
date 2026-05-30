<?php

namespace App\Tests\Repository;

use App\Entity\GithubPhpProject;
use App\Repository\GithubPhpProjectRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GithubPhpProjectRepositoryTest extends KernelTestCase
{
    private GithubPhpProjectRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->repository = $container->get(GithubPhpProjectRepository::class);
    }

    public function testSaveAndFind(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $project = new GithubPhpProject();
        $project->setRepositoryId(888001);
        $project->setName('test/find-project');
        $project->setUrl('https://github.com/test/find-project');
        $project->setCreatedDate(new DateTimeImmutable());
        $project->setStars(1000);

        $this->repository->save($project, true);

        $found = $this->repository->find($project->getId());
        $this->assertNotNull($found);
        $this->assertSame('test/find-project', $found->getName());

        $entityManager->remove($project);
        $entityManager->flush();
    }

    public function testFindOneByRepositoryId(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $project = new GithubPhpProject();
        $project->setRepositoryId(888002);
        $project->setName('test/findByRepoId');
        $project->setUrl('https://github.com/test/findByRepoId');
        $project->setCreatedDate(new DateTimeImmutable());
        $project->setStars(2000);

        $this->repository->save($project, true);

        $found = $this->repository->findOneBy(['repositoryId' => 888002]);
        $this->assertNotNull($found);
        $this->assertSame('test/findByRepoId', $found->getName());

        $entityManager->remove($project);
        $entityManager->flush();
    }

    public function testFindOneByReturnsNullForNonExistent(): void
    {
        $found = $this->repository->findOneBy(['repositoryId' => 999999999]);
        $this->assertNull($found);
    }

    public function testRemove(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $project = new GithubPhpProject();
        $project->setRepositoryId(888003);
        $project->setName('test/remove-project');
        $project->setUrl('https://github.com/test/remove-project');
        $project->setCreatedDate(new DateTimeImmutable());
        $project->setStars(500);

        $this->repository->save($project, true);
        $id = $project->getId();

        $this->repository->remove($project, true);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindAllReturnsProjects(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $project = new GithubPhpProject();
        $project->setRepositoryId(888004);
        $project->setName('test/find-all-project');
        $project->setUrl('https://github.com/test/find-all-project');
        $project->setCreatedDate(new DateTimeImmutable());
        $project->setStars(3000);

        $this->repository->save($project, true);

        $all = $this->repository->findAll();
        $this->assertNotEmpty($all);

        $entityManager->remove($project);
        $entityManager->flush();
    }
}
