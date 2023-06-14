<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'There is already a tag with this name.')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /** @var Collection<Post> $posts */
    #[ORM\ManyToMany(targetEntity: Post::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_post')]
    private Collection $posts;

    /* ---------- Constructor ---------- */

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->posts = new ArrayCollection();
    }

    /* ---------- Getters and setters ---------- */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return (string)$this->name;
    }

    public function setName(string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): Tag
    {
        $this->posts[] = $post;
        return $this;
    }

    public function removePost(Post $post): Tag
    {
        $this->posts->removeElement($post);
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}