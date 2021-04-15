<?php

namespace App\EventSubscriber;

use App\Entity\Category;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UpdateDepthSubscriber implements EventSubscriber
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::prePersist,
            Events::postRemove,
            Events::postUpdate,
            Events::preUpdate
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->logActivity('postPersist', $args);
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
//        $this->logActivity('prePersist', $args);
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
//        $this->logActivity('preFlush', $args);
    }


    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->logActivity('remove', $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
//        $this->logActivity('postUpdate', $args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->logActivity('preUpdate', $args);
    }

    private function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Category) {
            return;
        }
        $entity->setDepth(substr_count($entity->getRealMaterializedPath(), $entity::getMaterializedPathSeparator()));
        dump( sprintf('%s: %s %s', $action, $entity->getCode(),  $entity->getRealMaterializedPath() . ': ' . $entity->getDepth()));



        // ... get the entity information and log it somehow
    }
}
