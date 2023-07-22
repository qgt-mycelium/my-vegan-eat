<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $post;

    /** @var Collection<User> $likes */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'comment_like')]
    private Collection $likes;

    #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Comment $parent = null;

    /** @var Collection<Comment> $comments */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'parent', orphanRemoval: true)]
    private Collection $comments;

    /* ---------- Constructor ---------- */

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /* ---------- Getters and setters ---------- */

    /**
     * Get the value of id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the value of content.
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of isPublished.
     */
    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    /**
     * Set the value of isPublished.
     */
    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get the value of isDeleted.
     */
    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * Set the value of isDeleted.
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get the value of createdAt.
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of author.
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * Set the value of author.
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of post.
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * Set the value of post.
     */
    public function setPost(Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * @param User[] $likes
     */
    public function setLikes(array $likes): Comment
    {
        $this->likes = new ArrayCollection($likes);

        return $this;
    }

    public function addLike(User $user): Comment
    {
        if (!$this->likes->contains($user)) {
            $this->likes->add($user);
        }

        return $this;
    }

    public function removeLike(User $user): Comment
    {
        if ($this->likes->contains($user)) {
            $this->likes->removeElement($user);
        }

        return $this;
    }

    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    public function setParent(?Comment $parent): Comment
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     */
    public function setComments(array $comments): Comment
    {
        $this->comments = new ArrayCollection($comments);

        return $this;
    }

    /* ---------- Other ---------- */

    public function isLikedByUser(User $user): bool
    {
        return $this->likes->contains($user);
    }
}
