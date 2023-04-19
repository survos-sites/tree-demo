<?php

// https://cv.iptc.org/newscodes/mediatopic/

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Survos\Tree\Traits\TreeTrait;
use Survos\Tree\TreeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','transitions', 'rp']],
    denormalizationContext: ['groups' => ["Default", "minimum", "browse"]],
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'parentId' => 'exact', 'code' => 'exact'])]
#[ApiFilter(PropertyFilter::class)]
#[Gedmo\Tree(type:"nested")]
#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic implements \Stringable, RouteParametersInterface, TreeInterface
{
    use TreeTrait;
    use RouteParametersTrait;
    final const PLACE_NEW='new';
    const JOIN_COLUMN_NAME='id';

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'code', onDelete: 'CASCADE')]
    protected $parent;


    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10)]
    #[Groups(['minimum','search','jstree'])]
    #[ApiProperty(identifier: true)]
    private $code;
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['minimum','search','jstree'])]
    private $name;
    #[ORM\Column(type: 'text')]
    #[Groups(['minimum','search','jstree'])]
    private $description;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(referencedColumnName: 'code', onDelete: 'CASCADE')]
    private $root;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
    public function getRoot(): ?Topic
    {
        return $this->root;
    }
    public function setRoot(?Topic $root): self
    {
        $this->root = $root;

        return $this;
    }
    /**
     * @return Collection|Topic[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
    public function getData(): string|bool
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
    public function __toString(): string
    {
        return sprintf("%s %s", $this->getName(), $this->getCode());
    }
    public function getUniqueIdentifiers(): array
    {
        return ['topicId' => $this->getCode()];
    }

    #[Groups(['minimum','search','jstree'])]
    public function getParentId(): ?string
    {
        return $this->getParent() ? $this->getParent()->getCode() : null;
    }

    #[Groups(['minimum','search','jstree'])]
    public function getId(): ?string
    {
        return $this->getCode();
    }


}
