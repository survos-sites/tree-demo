<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @Gedmo\Tree(type="nested")
 * @ApiResource()
 * @ApiFilter(SearchFilter::class, properties={"building": "exact"})
 *
 */
#[ORM\Entity(repositoryClass: 'App\Repository\LocationRepository')]
class Location implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    /**
     * @Gedmo\Slug(fields={"name"})
     */
    #[ORM\Column(type: 'string', length: 32)]
    private $code;
    /**
     * @Gedmo\TreeLeft
     */
    #[ORM\Column(type: 'integer')]
    private $lft;
    /**
     * @Gedmo\TreeLevel
     */
    #[ORM\Column(type: 'integer')]
    private $lvl;
    /**
     * @Gedmo\TreeRight
     */
    #[ORM\Column(type: 'integer')]
    private $rgt;
    /**
     * @Gedmo\TreeRoot
     */
    #[ORM\ManyToOne(targetEntity: 'Location')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;
    /**
     * @Gedmo\TreeParent
     */
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: 'Location', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;
    #[ORM\OneToMany(targetEntity: 'Location', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $orderIdx;
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Building', inversedBy: 'locations')]
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
    /**
     * @return ?Location
     */
    public function getParent(): ?Location
    {
        return $this->parent;
    }
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
