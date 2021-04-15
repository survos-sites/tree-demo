<?php

// src/EventListener/SearchIndexer.php
namespace App\EventListener;

use App\Entity\Category;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

class UpdateTreeDepth
{
    // the listener methods receive an argument which gives you access to
    // both the entity object of the event and the entity manager itself
    public function preUpdate(PreUpdate $args): void
    {
        /** @var TreeNodeInterface $entity */
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Category) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $entity->setDepth(substr_count($entity->getRealMaterializedPath(), $entity::getMaterializedPathSeparator()));
//        dd($entity->getRealMaterializedPath(), $entity->getDepth());
        // ... do something with the Product entity
    }
}
