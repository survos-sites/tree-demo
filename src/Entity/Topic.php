<?php

// https://cv.iptc.org/newscodes/mediatopic/

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\BaseBundle\Entity\SurvosBaseEntity;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Annotation\Groups;

#[Gedmo\Tree(type:"nested")]
#[ApiResource(
    normalizationContext: ['groups' => ['Default','jstree','minimum', 'marking','transitions', 'rp']],
    denormalizationContext: ['groups' => ["Default", "minimum", "browse"]],
)]
#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic implements \Stringable, RouteParametersInterface
{
    use RouteParametersTrait;
    final const PLACE_NEW='new';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10)]
    #[Groups(['minimum','search','jstree'])]
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
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLeft]
    private $lft;
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLevel]
    private $lvl;
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeRight]
    private $rgt;
    #[ORM\ManyToOne(targetEntity: 'Topic')]
    #[ORM\JoinColumn(referencedColumnName: 'code', onDelete: 'CASCADE')]
    #[Gedmo\TreeRoot]
    private $root;
    #[ORM\ManyToOne(targetEntity: 'Topic', inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'code', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    private $parent;
    #[ORM\OneToMany(targetEntity: 'Topic', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;

    #[ORM\Column]
    private int $childCount = 0;
    public function __construct()
    {
        $this->children = new ArrayCollection();
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
    public function getRoot(): ?Topic
    {
        return $this->root;
    }
    public function setRoot(?Topic $root): self
    {
        $this->root = $root;

        return $this;
    }
    public function getParent(): ?Topic
    {
        return $this->parent;
    }
    public function setParent(?Topic $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
    /**
     * @return Collection|Topic[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
    public function addChild(Topic $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $this->childCount++;
            $child->setParent($this);
        }

        return $this;
    }
    public function removeChild(Topic $child): self
    {
        if ($this->children->removeElement($child)) {
            $this->childCount--;
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
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

    public function getChildCount(): ?int
    {
        return $this->childCount;
    }

    public function setChildCount(int $childCount): self
    {
        $this->childCount = $childCount;

        return $this;
    }

}
