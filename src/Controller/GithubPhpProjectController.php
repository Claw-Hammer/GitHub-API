<?php

namespace App\Controller;

use App\Repository\GithubPhpProjectRepository;
use App\Service\GithubApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\UX\Turbo\TurboStreamResponse;

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

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/refresh', name: 'github_projects_refresh', methods: ['POST'])]
    public function refresh(GithubApiService $service, GithubPhpProjectRepository $repository): TurboStreamResponse
    {
        $service->syncProjects();

        $projects = $repository->findBy([], ['stars' => 'DESC']);

        $content = $this->renderView('github_php_project/refresh.stream.html.twig', [
            'projects' => $projects,
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
