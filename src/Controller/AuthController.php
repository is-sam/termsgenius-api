<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $repeatPassword = $data['repeatPassword'] ?? null;

        if (!$email || !$password || !$repeatPassword) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        if ($password !== $repeatPassword) {
            return new JsonResponse(['error' => 'Passwords do not match'], 400);
        }

        /** @var User $user */
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $password
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }
}
