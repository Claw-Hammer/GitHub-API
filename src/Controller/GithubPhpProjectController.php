<?php

namespace App\Controller;

use App\Repository\GithubPhpProjectRepository;
use App\Service\GithubApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\Attribute\Stream;

#[Route('/github-projects')]
class GithubPhpProjectController extends AbstractController
{
    #[Route('', name: 'github_projects_index')]
    public function index(GithubPhpProjectRepository $repository): Response
    {
        $projects = $repository->findBy([], ['stars' => 'DESC']);

        return $this->render('github_php_project/index.html.twig', [
            'projects' => $projects,
        ]);
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

    #[Route('/refresh', name: 'github_projects_refresh', methods: ['POST'])]
    #[Stream]
    public function refresh(GithubApiService $service, GithubPhpProjectRepository $repository): Response
    {
        $service->syncProjects();

        $projects = $repository->findBy([], ['stars' => 'DESC']);

        return $this->render('github_php_project/refresh.stream.html.twig', [
            'projects' => $projects,
        ]);
    }
}
