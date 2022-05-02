<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
class User implements UserInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;
    #[ORM\Column(type: 'json')]
    private $roles = [];
    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private $password;
    #[ORM\OneToMany(targetEntity: 'App\Entity\Building', mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    private $buildings;
    public function __construct()
    {
        $this->buildings = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }
    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    /**
     * @return Collection|Building[]
     */
    public function getBuildings(): Collection
    {
        return $this->buildings;
    }
    public function addBuilding(Building $building): self
    {
        if (!$this->buildings->contains($building)) {
            $this->buildings[] = $building;
            $building->setUser($this);
        }

        return $this;
    }
    public function removeBuilding(Building $building): self
    {
        if ($this->buildings->contains($building)) {
            $this->buildings->removeElement($building);
            // set the owning side to null (unless already changed)
            if ($building->getUser() === $this) {
                $building->setUser(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->getEmail();
    }
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
        // TODO: Implement getUserIdentifier() method.
    }
}
