<?php

namespace App\Entity;

use ApiPlatform\Elasticsearch\Filter\OrderFilter;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\Tree\Traits\TreeTrait;
use Survos\Tree\TreeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;

#[ApiResource(
    normalizationContext: ['groups' => ['Default', 'jstree', 'minimum', 'marking', 'transitions', 'rp']],
    denormalizationContext: ['groups' => ["Default", "minimum", "browse"]],
)]
#[ApiFilter(OrderFilter::class, properties: ['marking', 'org', 'shortName', 'fullName'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial', 'isDir' => 'exact'])]
#[Gedmo\Tree(type: "nested")]
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements \Stringable, TreeInterface
{
    use TreeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['minimum', 'search', 'jstree'])]
    private $id;
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['minimum', 'search', 'jstree'])]
    private $name;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;
    #[ORM\Column(type: 'boolean')]
    #[Groups(['minimum', 'search', 'jstree'])]
    private $isDir;

//    private $children;
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

//    public function getId(): ?int
//    {
//        return $this->id;
//    }

    #[Groups(['minimum','search','jstree'])]
    public function getId(): ?string
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

    public function __toString(): string
    {
        return (string)$this->getName();
    }

    public function getExtension(): ?string
    {
        return pathinfo($this->getName(), PATHINFO_EXTENSION);
    }
}
