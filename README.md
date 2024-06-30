# JS-TREE and Doctrine Tree Extension

## Quick Start

```bash
git clone git@github.com:survos/jstree-demo.git
cd jstree-demo/
composer install
yarn install && yarn dev
bin/console d:database:create
bin/console d:schema:update --force --complete
bin/console doctrine:fixtures:load -n
bin/console app:import-topics 
bin/console app:load-directory-files  

# 
symfony proxy:domain:attach jstree-demo
symfony server:start -d

# OR php -S localhost:8300 -t public/
```


## Example

https://jstree-symfony-demo.herokuapp.com

## Relevant Links

This app uses Symfony, Doctrine, Doctrine Extensions, APIPlatform
and jquery-jstree.

This is a great read to understand how Nested Sets work:

    https://drib.tech/programming/hierarchical-data-relational-databases-symfony-4-doctrine

## Install Doctrine Tree Extension

```bash
composer req stof/doctrine-extensions-bundle
```

Modify the configuration
```yaml
# stof_doctrine_extensions.yaml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            sluggable: true
            tree: true

```

## Create the entity

```bash
bin/console make:entity File
    name, string, 80
    path, string, 80
```

Add the tree properties.  Change 'Location' to your class name if necessary.
Two parts, the header, set the slugger on 'code', and then add the properties.

```php 
// Location.php (the entity class)
use Gedmo\Mapping\Annotation as Gedmo; // <-- Add this

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
#[Gedmo\Tree(type:"nested")] // <-- Add This

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 * @Gedmo\Tree(type="nested") 
 */
 #[Gedmo\Tree(type:"nested") // <-- Add This
...
    /**
     * @ORM\Column(type="string", length=32)
     * @Gedmo\Slug(fields={"name"}) <-- add this
     */
    private $code;

```   

```php
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="children")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

```

Now generate the setters and getters.

```bash
bin/console make:entity App\\Entity\\Location --regenerate
```

Set the LocationRepository.php repository to extend the NestTreeRepsitory.  Replace the constructor
```php
class LocationRepository extends NestedTreeRepository // was ServiceEntityRepository
{

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Location::class));
    }

//    public function __construct(ManagerRegistry $registry)
//    {
//        parent::__construct($registry, Location::class);
//    }

}

```

Create a fixtures file to get start

```bash
composer require orm-fixtures --dev 
bin/console make:fixture
```

bin/console doctrine:schema:update --force
bin/console doctrine:fixtures:load -n



