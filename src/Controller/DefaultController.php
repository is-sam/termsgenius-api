<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class DefaultController extends AbstractController
{
    #[Route('/hello', name: 'hello')]
    public function hello(): Response
    {
        return new JsonResponse(['message' => 'Hello World!']);
    }
}
