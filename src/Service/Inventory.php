<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Building;
use App\Entity\Location;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class Inventory
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function forUser(User $user): Building
    {
        $building = $this->em->getRepository(Building::class)->findOneBy(['user' => $user], ['id' => 'ASC']);
        if (!$building) {
            $building = new Building($user->getUserIdentifier());
            $building->setCode('inventory-'.bin2hex(random_bytes(8)));
            $user->addBuilding($building);
            $this->em->persist($building);
        }
        if (!$building->getRootLocation()) {
            $building->addLocation(new Location(mb_substr($user->getUserIdentifier(), 0, 80)));
        }
        $this->em->flush();

        return $building;
    }
}
