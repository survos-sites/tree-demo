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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Survos\Tree\Traits\TreeTrait;
use Survos\Tree\TreeInterface;
use Symfony\Component\Serializer\Attribute\Groups;

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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 10, unique: true)]
    #[Groups(['minimum','search','jstree'])]
    #[ApiProperty(identifier: true)]
    private ?string $code = null;
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['minimum','search','jstree'])]
    private ?string $name = null;
    #[ORM\Column(type: 'text')]
    #[Groups(['minimum','search','jstree'])]
    private ?string $description = null;

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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
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


}
