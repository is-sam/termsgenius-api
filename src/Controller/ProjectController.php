<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Project;
use App\Enum\MessageOwner;
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
        $project = $this->projectRepository->findOneBy([
            'id' => $id,
            'user' => $this->getUser(),
        ]);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

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

    #[Route('/{id}/messages', name: 'messages', methods: ['GET'])]
    public function messages(int $id): JsonResponse
    {
        $project = $this->projectRepository->findOneBy([
            'id' => $id,
            'user' => $this->getUser(),
        ]);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $messages = array_map(fn ($message) => $this->serializeMessage($message), $project->getMessages()->toArray());

        return new JsonResponse($messages);
    }

    #[Route('/{id}/messages', name: 'post_message', methods: ['POST'])]
    public function postMessage(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = $this->projectRepository->find($id);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        if ($data['text'] === null || trim($data['text']) === '') {
            return new JsonResponse(['error' => 'Text is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($data['text']) < 3) {
            return new JsonResponse(['error' => 'Text is too short'], Response::HTTP_BAD_REQUEST);
        }

        $message = new Message();
        $message->setText($data['text']);
        $message->setOwner('user');
        $message->setCreateAt(new \DateTimeImmutable());
        $message->setProject($project);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return new JsonResponse($this->serializeMessage($message), Response::HTTP_CREATED);
    }

    protected function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->getId(),
            'text' => $message->getText(),
            'owner' => $message->getOwner(),
            'createAt' => $message->getCreateAt(),
        ];
    }
}
