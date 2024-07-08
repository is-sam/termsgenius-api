<?php

namespace App\EventListener;

use App\Attribute\UserAware;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
class SetUserFilterListener
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!($user = $this->security->getUser()) || !$user instanceof User) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $filter = $this->entityManager->getFilters()->enable('user_filter');
        $filter->setParameter('user_id', $user->getId());

        // For POST requests, set the user before persisting
        // $request = $event->getRequest();
        // if ($request->isMethod('POST')) {
        //     $data = json_decode($request->getContent(), true);
        //     if ($data) {
        //         // Determine the entity class from the request attributes
        //         $entityClass = $request->attributes->get('_api_resource_class');
        //         if ($entityClass && $this->isUserAwareEntity($entityClass)) {
        //             $data['user'] = "/api/users/{$user->getId()}"; // Set the user ID
        //             $request->request->replace($data);
        //         }
        //     }
        // }
    }

    private function isUserAwareEntity(string $entityClass): bool
    {
        $reflectionClass = new ReflectionClass($entityClass);
        $attributes = $reflectionClass->getAttributes(UserAware::class);

        return count($attributes) > 0;
    }
}
