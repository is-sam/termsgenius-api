<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projects', name: 'api_project_')]
class ProjectController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ProjectRepository $projectRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $projects = $this->projectRepository->getListForUser($this->getUser());

        return new JsonResponse($projects);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $project = $this->projectRepository->find($id);

        return new JsonResponse($this->serialize($project));
    }


    #[Route('', name: 'post', methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = new Project();
        $project->setTitle($data['title']);
        $project->setContent($data['content']);
        $project->setUser($this->getUser());
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return new JsonResponse($this->serialize($project), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'put', methods: ['PUT'])]
    public function put(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = $this->projectRepository->find($id);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        if ($data['title']) {
            $project->setTitle($data['title']);
        }

        if ($data['content']) {
            $project->setContent($data['content']);
        }

        $this->entityManager->flush();

        return new JsonResponse($this->serialize($project));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($project);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function serialize(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'title' => $project->getTitle(),
            'content' => $project->getContent(),
            'createdAt' => $project->getCreatedAt(),
            'updatedAt' => $project->getUpdatedAt(),
        ];
    }
}
