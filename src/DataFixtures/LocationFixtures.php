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

        foreach (['Condo' => 'Bedroom 1,Bedroom 2,Bathroom', 'House'=>'First Floor,Second Floor', 'Warehouse' => 'Storage Area 1, Storage Area 2']
                 as $buildingName => $areas) {
            $building = (new Building($buildingName));
            $user
                ->addBuilding($building);

            // root
            $rootLocation = (new Location($buildingName));
            $building->addLocation($rootLocation);
           // $manager->persist($rootLocation);

            foreach (explode(',', $areas) as $area) {
                $location = (new Location($area))
                    ->setParent($rootLocation);
            }
            // $manager->persist($location);

        }




        /*
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
        */


        $manager->flush();

    }
}
