<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/usage', name: 'usage_')]
class UsageController extends AbstractController
{
    #[Route('/check', name: 'check')]
    function check(MessageRepository $messageRepository): JsonResponse {
        /** @var User */
        $user = $this->getUser();
        $monthStart = new \DateTime('first day of this month');
        $monthEnd = new \DateTime('last day of this month');

        $messages = $messageRepository->createQueryBuilder('message')
            ->select('project.id', 'count(message.id) as count')
            ->join('message.project', 'project')
            ->where('project.user = :user')
            ->andWhere('message.createAt >= :date_start')
            ->andWhere('message.createAt <= :date_end')
            ->andWhere('message.owner = :owner')
            ->groupBy('project.id')
            ->setParameter('user', $user)
            ->setParameter('date_start', $monthStart)
            ->setParameter('date_end', $monthEnd)
            ->setParameter('owner', 'user')
            ->getQuery()
            ->getResult();

        return $this->json([
            'projects' => count($messages),
            'messages' => array_sum(array_column($messages, 'count')),
            'detail' => $messages,
        ]);
    }
}
