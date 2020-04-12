<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // a root is a locatino with no parent
        $root = (new Location())->setName('House');
        $manager->persist($root);

        $area = [];
        foreach (['Basement', 'First Floor', 'Second Floor', 'Attic', 'Garage'] as $idx => $name) {
            $location = (new Location())
                ->setName($name)
                ->setParent($root);
            $area[$name] = $location;
            $manager->persist($location);
        }
        $closet = (new Location())
            ->setName('Linen Closet')
            ->setParent($area['Second Floor']);

        $manager->persist($location);

        $manager->flush();

    }
}
