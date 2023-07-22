<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[UniqueEntity(fields: ['title'], message: 'There is already an post with this title.')]
#[UniqueEntity(fields: ['slug'], message: 'There is already an post with this slug.')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $slug;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 20)]
    private string $content;

    /** @var Collection<User> $likes */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_like')]
    private Collection $likes;

    /** @var Collection<User> $favorites */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_favorite')]
    private Collection $favorites;

    /** @var Collection<Tag> $tags */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'posts')]
    private Collection $tags;

    /** @var Collection<Category> $categories */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'posts')]
    private Collection $categories;

    /** @var Collection<Comment> $comments */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private \DateTimeInterface|null $publishedAt;

    /* ---------- Constructor ---------- */

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /* ---------- Getters and setters ---------- */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return (string) $this->title;
    }

    public function getSlug(): string
    {
        return (string) $this->slug;
    }

    public function getContent(): string
    {
        return (string) $this->content;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function setTitle(string $title): Post
    {
        $this->title = $title;

        return $this;
    }

    public function setSlug(string $slug): Post
    {
        $this->slug = $slug;

        return $this;
    }

    public function setContent(string $content): Post
    {
        $this->content = $content;

        return $this;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): Post
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function addLike(User $user): Post
    {
        if (!$this->likes->contains($user)) {
            $this->likes->add($user);
        }

        return $this;
    }

    public function removeLike(User $user): Post
    {
        if ($this->likes->contains($user)) {
            $this->likes->removeElement($user);
        }

        return $this;
    }

    /**
     * @param User[] $likes
     */
    public function setLikes(array $likes): Post
    {
        $this->likes = new ArrayCollection($likes);

        return $this;
    }

    public function addFavorite(User $user): Post
    {
        if (!$this->favorites->contains($user)) {
            $this->favorites->add($user);
        }

        return $this;
    }

    public function removeFavorite(User $user): Post
    {
        if ($this->favorites->contains($user)) {
            $this->favorites->removeElement($user);
        }

        return $this;
    }

    /**
     * @param User[] $favorites
     */
    public function setFavorites(array $favorites): Post
    {
        $this->favorites = new ArrayCollection($favorites);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): Post
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addPost($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): Post
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removePost($this);
        }

        return $this;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags(array $tags): Post
    {
        $this->tags = new ArrayCollection($tags);

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): Post
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addPost($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): Post
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removePost($this);
        }

        return $this;
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories(array $categories): Post
    {
        $this->categories = new ArrayCollection($categories);

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
    public function setComments(array $comments): Post
    {
        $this->comments = new ArrayCollection($comments);

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): Post
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): Post
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /* ---------- Other ---------- */

    public function isLikedByUser(User $user): bool
    {
        return $this->likes->contains($user);
    }

    public function isFavoritedByUser(User $user): bool
    {
        return $this->favorites->contains($user);
    }
}
