<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ProjectRepository $projectRepository,
    ) {
    }

    #[Route('', name: 'data', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $projects = $this->projectRepository->findBy([
            'user' => $this->getUser(),
        ]);

        $numberOfProjects = count($projects);
        $numberOfMessages = 0;
        foreach ($projects as $project) {
            $userMessages = array_filter($project->getMessages()->toArray(), fn($message) => $message->getOwner() === 'user');
            $numberOfMessages += count($userMessages);
        }
        $numberOfLines = 0;
        foreach ($projects as $project) {
            $numberOfLines += count(explode("\n", $project->getContent()));
        }
        $timeSaved = $numberOfLines * 0.1;

        return new JsonResponse([
            'documents' => $numberOfProjects,
            'questions' => $numberOfMessages,
            'lines' => $numberOfLines,
            'timesaved' => $timeSaved,
        ]);
    }
}
