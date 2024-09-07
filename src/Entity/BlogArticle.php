<?php

namespace App\Entity;

use App\Repository\BlogArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ArticleStatus;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlogArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BlogArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The Author ID must not be blank.')]
    #[Assert\Type(type: 'integer', message: 'The Author ID must be a integer.')]
    private ?int $authorId = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'The title must not be blank.')]
    #[Assert\Length(max: 100, maxMessage: 'The title cannot be longer than {{ limit }} characters')]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\IsNull]
    private ?\DateTimeInterface $publicationDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'The content must not be blank.')]
    #[Assert\Length(min: 10, maxMessage: 'The content must be at least {{ limit }} characters long')]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank(message: 'Keywords must be provided.')]
    #[Assert\Type(type: 'array', message: 'Keywords must be a valid array.')]
    private array $keywords = [];

    #[Assert\NotBlank(message: 'The status must be not blank.')]
    #[Assert\Choice(choices: [ArticleStatus::DRAFT, ArticleStatus::PUBLISHED, ArticleStatus::DELETED])]
    #[ORM\Column(type: Types::STRING, enumType: ArticleStatus::class)]
    private ArticleStatus $status;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The slug must be not blank.')]
    #[Assert\Length(max: 255, maxMessage: 'The slug cannot be longer than {{ limit }} characters')]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $coverPictureRef = null;

    public function __construct() {
        $this->creationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): static
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function setStatus(ArticleStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCoverPictureRef(): ?string
    {
        return $this->coverPictureRef;
    }

    public function setCoverPictureRef(string $coverPictureRef): static
    {
        $this->coverPictureRef = $coverPictureRef;

        return $this;
    }
}
