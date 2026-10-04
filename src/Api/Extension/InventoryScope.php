<?php

declare(strict_types=1);

namespace App\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Location;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class InventoryScope implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private readonly Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->scope($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->scope($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function scope(QueryBuilder $query, QueryNameGeneratorInterface $names, string $class): void
    {
        if ($class !== Location::class) {
            return;
        }
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $query->andWhere('1 = 0');
            return;
        }
        $building = $names->generateJoinAlias('inventory_building');
        $owner = $names->generateParameterName('inventory_owner');
        $query->join($query->getRootAliases()[0].'.building', $building)
            ->andWhere($building.'.user = :'.$owner)->setParameter($owner, $user);
    }
}
