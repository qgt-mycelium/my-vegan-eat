<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    /** @var array<string> $roles */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[Assert\Length(min: 6)]
    private string $password;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $username;

    /* ---------- Getters and setters ---------- */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return (string)$this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }
}
