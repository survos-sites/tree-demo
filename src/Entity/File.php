<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\Tree\Traits\TreeTrait;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','transitions', 'rp']],
    denormalizationContext: ['groups' => ["Default", "minimum", "browse"]],
)]
#[ApiFilter(OrderFilter::class, properties: ['marking', 'org', 'shortName', 'fullName'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(SearchFilter::class, properties: ['id'=> 'exact', 'name' => 'partial', 'isDir' => 'exact'])]
#[Gedmo\Tree(type: "nested")]
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements \Stringable
{
    use TreeTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['minimum','search','jstree'])]
    private $id;
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['minimum','search','jstree'])]
    private $name;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;
    #[ORM\Column(type: 'boolean')]
    #[Groups(['minimum','search','jstree'])]
    private $isDir;
//    #[Gedmo\TreeLeft]
//    #[ORM\Column(type: 'integer')]
//    private $lft;
//    #[Gedmo\TreeLevel]
//    #[ORM\Column(type: 'integer')]
//    private $lvl;
//    #[Gedmo\TreeRight]
//    #[ORM\Column(type: 'integer')]
//    private $rgt;
//    #[Gedmo\TreeRoot]
//    #[ORM\ManyToOne(targetEntity: 'File')]
//    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
//    private $root;
//    #[Gedmo\TreeParent]
//    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
//    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
//    private $parent;
//    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
//    #[ORM\OrderBy(['left' => 'ASC'])]
//    private $children;
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
//    public function getLft(): ?int
//    {
//        return $this->lft;
//    }
//    public function setLft(int $lft): self
//    {
//        $this->lft = $lft;
//
//        return $this;
//    }
    public function getLvl(): ?int
    {
        return $this->level;
    }
//    public function setLvl(int $lvl): self
//    {
//        $this->lvl = $lvl;
//
//        return $this;
//    }
//    public function getRoot(): self
//    {
//        return $this->root;
//    }
//    public function setRoot(?File $root): self
//    {
//        $this->root = $root;
//
//        return $this;
//    }
    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getExtension(): ?string
    {
        return pathinfo($this->getName(), PATHINFO_EXTENSION);
    }
}
