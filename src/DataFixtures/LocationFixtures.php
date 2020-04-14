<?php

namespace App\DataFixtures;

use App\Entity\Building;
use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function loadYaml(ObjectManager $manager)
    {
        $yaml = <<< END
Basement:
FirstFloor:
    children:
END;

    }
    public function load(ObjectManager $manager)
    {
        $user = (new User())
            ->setEmail('tacman@gmail.com')
            ->setPassword('abc')
            ;
        $manager->persist($user);

        $building = (new Building())
            ->setName('House 1');
        $manager->persist($building);

        $user
            ->addBuilding($building);

        // a root is a locatino with no parent
        $root = (new Location())->setName('House');
        $manager->persist($root);
        $building
            ->addLocation($root);

        $area = [];
        foreach (['Basement', 'First Floor', 'Second Floor', 'Attic', 'Garage'] as $idx => $name) {
            $location = (new Location())
                ->setName($name)
                ->setParent($root);
            $building
                ->addLocation($location);
            $area[$name] = $location;
            $manager->persist($location);
        }
        $closet = (new Location())
            ->setName('Linen Closet')
            ->setParent($area['Second Floor']);
        $building
            ->addLocation($closet);
        $manager->persist($closet);


        $manager->flush();

    }
}
