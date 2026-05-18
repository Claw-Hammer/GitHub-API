<?php

namespace App\Controller;

use App\Repository\GithubPhpProjectRepository;
use App\Service\GithubApiService;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboStreamResponse;

#[Route('/github-projects')]
class GithubPhpProjectController extends AbstractController
{
    #[Route('', name: 'github_projects_index')]
    public function index(GithubPhpProjectRepository $repository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $qb = $repository->createQueryBuilder('p')
            ->orderBy('p.stars', 'DESC');

        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);

        return $this->render('github_php_project/index.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    #[Route('/refresh', name: 'github_projects_refresh', methods: ['POST'])]
    public function refresh(GithubApiService $service, GithubPhpProjectRepository $repository): TurboStreamResponse
    {
        $service->syncProjects();

        $qb = $repository->createQueryBuilder('p')
            ->orderBy('p.stars', 'DESC');

        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);

        $content = $this->renderView('github_php_project/refresh.stream.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);

        return new TurboStreamResponse($content);
    }

    #[Route('/{id}', name: 'github_projects_detail')]
    public function detail(GithubPhpProjectRepository $repository, int $id): Response
    {
        $project = $repository->find($id);

        if ($project === null) {
            throw $this->createNotFoundException('Project not found.');
        }

        return $this->render('github_php_project/detail.html.twig', [
            'project' => $project,
        ]);
    }
}
