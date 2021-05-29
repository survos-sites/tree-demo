<?php


namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class SurvosNestedTreeRepository extends NestedTreeRepository
{

    /**
     * @param string $entityClass The class name of the entity this repository manages
     *
     * @psalm-param class-string<T> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $manager = $registry->getManagerForClass($entityClass);
        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }


}
