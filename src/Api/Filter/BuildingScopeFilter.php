<?php
namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Building;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class BuildingScopeFilter implements FilterInterface
{
    private $token;
    private $requestStack;

    public function __construct(TokenStorageInterface $token, RequestStack $requestStack)
    {
        $this->token = $token;
        $this->requestStack = $requestStack;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (method_exists($resourceClass, 'setBuilding')) {
            $filteredBuilding = $this->requestStack->getCurrentRequest()->get('buildingId');
            // @todo: get the building from the url, check permissions
//            $this->addBuildingFilter($queryBuilder, $filteredBuilding);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Building $building
     */
    private function addBuildingFilter($queryBuilder, $building)
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere($alias.'.building = :building')
            ->setParameter('building', $building);
    }

    public function getDescription(string $resourceClass) : array
    {
        return [];
    }
}
