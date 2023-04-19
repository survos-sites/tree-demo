<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Survos\Tree\Traits\TreeTrait;
use Survos\Tree\TreeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[Gedmo\Tree(type: 'nested')]
#[ApiResource(
    normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','rp']],
)]
#[ApiResource(
    uriTemplate: '/building/{buildingId}/locations.{_format}',
    shortName: 'Location',
    operations: [new GetCollection(
        name: 'building_locations',
        normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','rp']],
    )],
    uriVariables: [
        'buildingId' => new Link(
            fromProperty: 'locations',
            fromClass: Building::class,
        ),
    ],
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['building' => 'exact'])]
class Location implements \Stringable, RouteParametersInterface, TreeInterface
{
    use RouteParametersTrait;
    use TreeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'string', length: 32)]
    #[Gedmo\Slug(fields: ['name'])]
    private $code;
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeRoot]
    private $root;
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: 'Location', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    protected $parent;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $orderIdx;
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private Building $building;
    public function __construct(#[ORM\Column(type: 'string', length: 80)] private ?string $name = null)
    {
        $this->children = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function getRoot(): ?self
    {
        return $this->root;
    }
    public function setRoot(?self $root): self
    {
        $this->root = $root;

        return $this;
    }
    public function getParent(): ?Location
    {
        return $this->parent;
    }
    #[Groups(['Default'])]
    public function getParentId(): ?int
    {
        return $this->getParent() ? $this->getParent()->getId() : null;
    }

    public function setParent(?TreeInterface $parent): TreeInterface
    {
        /** @var Location $parent */
        if ($parent) {
            $parent->getBuilding()->addLocation($this);
        }
        $this->parent = $parent;
        return $this;
    }


    /**
     * @return Collection|Location[]
     */
    public function getOrderIdx(): ?int
    {
        return $this->orderIdx;
    }
    public function setOrderIdx(?int $orderIdx): self
    {
        $this->orderIdx = $orderIdx;

        return $this;
    }
    public function __toString(): string
    {
        return (string) $this->getName(); // could expand parents
    }
    public function getBuilding(): ?Building
    {
        return $this->building;
    }
    public function setBuilding(?Building $building): self
    {
        $this->building = $building;

        return $this;
    }
}
