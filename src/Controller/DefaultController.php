<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class DefaultController extends AbstractController
{
    #[Route('/hello', name: 'hello')]
    public function hello(): Response
    {
        return new JsonResponse(['message' => 'Hello World!']);
    }

    #[Route('/login/register', name: 'register')]
    public function register(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'test123'));
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse(['message' => 'User Created!']);
    }
}
