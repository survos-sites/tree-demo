<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;

#[ORM\Entity(repositoryClass: 'App\Repository\BuildingRepository')]
class Building  implements \Stringable, RouteParametersInterface
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: false)]
    private $id;
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', inversedBy: 'buildings')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
    #[ORM\OneToMany(targetEntity: 'App\Entity\Location', mappedBy: 'building', orphanRemoval: true, cascade: ['persist'])]
    private $locations;
    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    #[ApiProperty(identifier: true)]
    private $code;
    public function __construct(#[ORM\Column(type: 'string', length: 255)] private ?string $name = null)
    {
        $this->locations = new ArrayCollection();
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
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }
    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setBuilding($this);
        }

        return $this;
    }
    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getBuilding() === $this) {
                $location->setBuilding(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return (string)$this->getName();
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

    public function getUniqueIdentifiers(): array
    {
        return ['buildingId' => $this->getCode()];
    }

    public function getRootLocation(): ?Location
    {
        // or filter by no parent?
        return $this->getLocations()->first() ?: null;
    }
}
