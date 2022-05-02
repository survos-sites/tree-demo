<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: "nested")]
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'string', length: 255)]
    private $name;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;
    #[ORM\Column(type: 'boolean')]
    private $isDir;
    #[Gedmo\TreeLeft]

    #[ORM\Column(type: 'integer')]
    private $lft;

    #[Gedmo\TreeLevel]
    #[ORM\Column(type: 'integer')]
    private $lvl;
    #[Gedmo\TreeRight]
    #[ORM\Column(type: 'integer')]
    private $rgt;
    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'File', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;
    #[ORM\OneToMany(targetEntity: 'File', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
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
    public function getPath(): ?string
    {
        return $this->path;
    }
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }
    public function getIsDir(): ?bool
    {
        return $this->isDir;
    }
    public function setIsDir(bool $isDir): self
    {
        $this->isDir = $isDir;

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
    public function getRoot(): ?File
    {
        return $this->root;
    }
    public function setRoot(?File $root): self
    {
        $this->root = $root;

        return $this;
    }
    public function getParent(): ?File
    {
        return $this->parent;
    }
    public function setParent(?File $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
    /**
     * @return Collection|File[]
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
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
    public function getChildCount(): int
    {
        return $this->getChildren()->count();
    }
    public function getParentId(): ?int
    {
        return $this->getParent() ? $this->getParent()->getId() : null;
    }
    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
