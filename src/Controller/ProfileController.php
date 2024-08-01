<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/profile', name: 'api_profile_')]
class ProfileController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ProjectRepository $projectRepository,
    ) {
    }

    #[Route('/data', name: 'data', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not valid'], Response::HTTP_NOT_IMPLEMENTED);
        }

        return new JsonResponse($this->serialize($user));
    }

    #[Route('/data', name: 'update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not valid'], Response::HTTP_NOT_IMPLEMENTED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $firstname = $data['firstname'] ?? null;
        $lastname = $data['lastname'] ?? null;

        if (!$firstname && !$lastname) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        if ($firstname) {
            $user->setFirstname($firstname);
        }

        if ($lastname) {
            $user->setLastname($lastname);
        }

        $this->entityManager->flush();

        return new JsonResponse($this->serialize($user), Response::HTTP_OK);
    }

    #[Route('/password', name: 'password', methods: ['PUT'])]
    public function password(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not valid'], Response::HTTP_NOT_IMPLEMENTED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $password = $data['password'] ?? null;
        $repeatPassword = $data['repeatPassword'] ?? null;

        if (!$password || !$repeatPassword) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        if ($password !== $repeatPassword) {
            return new JsonResponse(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $password
            )
        );

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Password successfully updated'], Response::HTTP_OK);
    }

    protected function serialize(User $user): array
    {
        return [
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
        ];
    }
}
