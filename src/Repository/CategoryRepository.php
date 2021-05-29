<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\DoctrineBehaviors\ORM\Tree\TreeTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    use TreeTrait; // materialized tree example!

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Manipulates the flat tree query builder before executing it. Override this method to customize the tree query
     */
    protected function addFlatTreeConditions(QueryBuilder $queryBuilder, array $extraParams): void
    {
        $extraParams = (new OptionsResolver())
            ->setDefaults([
                'depth' => false,
                'operation' => "<="
            ])->resolve($extraParams);
        if ($depth = $extraParams['depth']) {
            $queryBuilder->andWhere('t.depth ' . $extraParams['operation'] . ':depth')
            ->setParameter('depth', $depth);
        }
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
