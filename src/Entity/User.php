<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
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

    /* ---------- Vich uploader ---------- */

    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'avatarFileName')]
    #[Assert\Image(
        allowPortrait: false,
        allowLandscape: false,
    )]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $avatarFileName = null;

    /* ---------- Timestampable ---------- */

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastPasswordChangeAt = null;

    /* ---------- Constructor ---------- */

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /* ---------- Getters and setters ---------- */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
        return (string) $this->password;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getLastPasswordChangeAt(): ?\DateTimeInterface
    {
        return $this->lastPasswordChangeAt;
    }

    public function setLastPasswordChangeAt(\DateTimeInterface $lastPasswordChangeAt): void
    {
        $this->lastPasswordChangeAt = $lastPasswordChangeAt;
    }

    /* ---------- Vich uploader ---------- */

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(File $avatarFile = null): User
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getAvatarFileName(): ?string
    {
        return $this->avatarFileName;
    }

    public function setAvatarFileName(?string $avatarFileName): void
    {
        $this->avatarFileName = $avatarFileName;
    }

    /* ---------- PreUpdate ---------- */

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /* ---------- Serializable  ---------- */

    public function __serialize()
    {
        return [
            $this->id,
            $this->email,
            $this->password,
            $this->avatarFileName,
        ];
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function __unserialize(array $serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->avatarFileName,
        ) = $serialized;
    }
}
