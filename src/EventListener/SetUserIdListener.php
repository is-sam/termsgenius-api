<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

// #[AsEventListener(event: Events::prePersist)]
class SetUserIdListener implements EventSubscriber
{
    public function __construct(
        protected Security $security
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function onPrePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Project) {
            return;
        }

        $user = $this->security->getUser();
        if ($user) {
            $entity->setUser($user);
        }
    }
}
