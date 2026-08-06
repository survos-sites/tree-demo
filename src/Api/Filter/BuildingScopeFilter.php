<?php
namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
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
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (method_exists($resourceClass, 'setBuilding')) {
            $request = $this->requestStack->getCurrentRequest();
            $filteredBuilding = $request->attributes->get('buildingId') ?? $request->query->get('buildingId') ?? $request->request->get('buildingId');
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
