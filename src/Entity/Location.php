<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[Gedmo\Tree(type: 'nested')]
#[ApiResource(
    normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','rp']],
)]

#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['building' => 'exact'])]
class Location implements \Stringable, RouteParametersInterface
{
    use RouteParametersTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'string', length: 32)]
    #[Gedmo\Slug(fields: ['name'])]
    private $code;
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLeft]
    private $lft;
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLevel]
    private $lvl;
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeRight]
    private $rgt;
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeRoot]
    private $root;
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: 'Location', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    private $parent;
    #[ORM\OneToMany(targetEntity: 'Location', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $orderIdx;
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Building::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private $building;
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
    public function getLft(): ?int
    {
        return $this->lft;
    }
    public function setLft(int $lft): self
    {
        $this->lft = $lft;

        return $this;
    }
    public function getLvl(): ?int
    {
        return $this->lvl;
    }
    public function setLvl(int $lvl): self
    {
        $this->lvl = $lvl;

        return $this;
    }
    public function getRgt(): ?int
    {
        return $this->rgt;
    }
    public function setRgt(int $rgt): self
    {
        $this->rgt = $rgt;

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
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        if ($parent) {
            $this->parent->getBuilding()->addLocation($this);
        }

        return $this;
    }
    /**
     * @return Collection|Location[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
    public function addChild(Location $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }
    public function removeChild(Location $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
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
