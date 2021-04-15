<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class CategoryFixtures extends Fixture
{
    private LoggerInterface $logger;
    private array $list;
    private CategoryRepository $categoryRepository;

    public function __construct(LoggerInterface $logger, CategoryRepository $categoryRepository) {

        $this->logger = $logger;
        $this->list = [];
        $this->categoryRepository = $categoryRepository;
    }
    public function loadYaml() {
        $yaml = <<< END
Root:
    - n: Animals
      c:
        - n: Mammals 
          c:
            - n: Dolphin
            - n: Elephant
              c: 
                - n: African
                - n: Asian
        - n: Birds 
          c:
            - n: Hawk
            - n: Eagle
END;
        return Yaml::parse($yaml, Yaml::PARSE_OBJECT);
    }


    private function recursiveLoad($x, ObjectManager $entityManager, TreeNodeInterface $parent=null)
    {
        foreach ($x as $idx => $categoryObject) {
//            $this->logger->info("Loading categoryObject", $categoryObject);
            assert(!empty($categoryObject['n']), "Missing name " . json_encode($x));
            $category = new Category($categoryObject['n'], $parent);
            $entityManager->persist($category);
            $this->list[$category->getId()] = $category;
            if (!empty($categoryObject['c'])) {
                $this->recursiveLoad($categoryObject['c'], $entityManager, $category);
            }
        }
    }


        public function load(ObjectManager $entityManager)
    {
        $data = $this->loadYaml();

//        $root = new Category('Root'); // necessary??
//        $entityManager->persist($root); // to get the ID
        $this->recursiveLoad($data['Root'], $entityManager);
        /**
         * @var Category $category
         */
        foreach ($this->list as $mpath => $category) {
            $this->logger->info($mpath, [$category->getCode(), $category->getMaterializedPath()]);
        }
        /** @var Category $ele */
        /** @var Category $birds */
        $ele = $this->list['elephant'];
        $birds = $this->list['birds'];
        $animals = $this->list['animals'];
//        dd($birds->toArray(), $birds->toFlatArray());
        $eleParent = $ele->getParentNode();
        $this->logger->info("birds, ele, eleParent", [$birds->getRealMaterializedPath(), $ele->getRealMaterializedPath(), $eleParent->getRealMaterializedPath()]);
            $entityManager->flush();
        $ele->setParentNode($animals);
            $eleParent = $ele->getParentNode();
            $this->logger->info("after birds, ele, eleParent", [$birds->getRealMaterializedPath(), $ele->getRealMaterializedPath(), $eleParent->getRealMaterializedPath()]);

        $entityManager->flush();
        $birdsEntity = $this->categoryRepository->findOneBy(['id' => 'birds']);
        assert(!empty($birdsEntity));
//        dump($birdsEntity, $birdsEntity->toArray());
//        dd($birds->toArray());

        return;
//        dd($birds->getMaterializedPath(), $ele->getMaterializedPath(), $ele->getParentNode()->getMaterializedPath());

        dd($this->list);


//        dd($data);


        dd($root->getChildNodes(), $data);

        foreach (['Condo' => 'Bedroom 1,Bedroom 2,Bathroom',
                     'House'=>'First Floor,Second Floor', 'Warehouse' => 'Storage Area 1, Storage Area 2']
                 as $buildingName => $areas) {
            $building = (new Category($buildingName));
            $user->addBuilding($building);
        }

        /** @var Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface $category */
        $category = new Category();
        $category->setId(1);

        $child = new Category();
        $child->setId(2);

        $child->setChildNodeOf($category);

        $entityManager->persist($child);
        $entityManager->persist($category);
        $entityManager->flush();

        $categoryRepository = $entityManager->getRepository(Category::class);

        $root = $categoryRepository->getTree();

        $root->getParentNode(); // null
        $root->getChildNodes(); // ArrayCollection
        $root[0][1]; // node or null
        $root->isLeafNode(); // boolean
        $root->isRootNode(); // boolean

        $manager->flush();
    }
}
