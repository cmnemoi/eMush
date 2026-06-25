<?php

declare(strict_types=1);

namespace Mush\MetaGame\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\MetaGame\Entity\Poll\Poll;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    paginationItemsPerPage: 25,
    normalizationContext: ['groups' => ['news_read']],
    denormalizationContext: ['groups' => ['news_write']],
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter', 'news.search_filter', 'news.order_filter', 'date.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN") or is_granted("NEWS_IS_PUBLISHED", object)',
        ),
        new Put(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
    ],
)]
#[ORM\Entity]
#[ORM\Table(name: 'news')]
class News
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['news_read'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['news_read', 'news_write'])]
    private string $frenchTitle;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Groups(['news_read', 'news_write'])]
    private string $frenchContent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['news_read', 'news_write'])]
    private ?string $englishTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['news_read', 'news_write'])]
    private ?string $englishContent = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['news_read', 'news_write'])]
    private ?string $spanishTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['news_read', 'news_write'])]
    private ?string $spanishContent = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[Groups(['news_read', 'news_write'])]
    private bool $isPinned = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['news_read', 'news_write'])]
    private ?\DateTime $publicationDate = null;

    #[ORM\OneToOne(targetEntity: Poll::class)]
    #[Groups(['news_read', 'news_write'])]
    private ?Poll $poll = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFrenchTitle(): string
    {
        return $this->frenchTitle;
    }

    public function setFrenchTitle(string $frenchTitle): void
    {
        $this->frenchTitle = $frenchTitle;
    }

    public function getFrenchContent(): string
    {
        return $this->frenchContent;
    }

    public function setFrenchContent(string $frenchContent): void
    {
        $this->frenchContent = $frenchContent;
    }

    public function getEnglishTitle(): ?string
    {
        return $this->englishTitle;
    }

    public function setEnglishTitle(?string $englishTitle): void
    {
        $this->englishTitle = $englishTitle;
    }

    public function getEnglishContent(): ?string
    {
        return $this->englishContent;
    }

    public function setEnglishContent(?string $englishContent): void
    {
        $this->englishContent = $englishContent;
    }

    public function getSpanishTitle(): ?string
    {
        return $this->spanishTitle;
    }

    public function setSpanishTitle(?string $spanishTitle): void
    {
        $this->spanishTitle = $spanishTitle;
    }

    public function getSpanishContent(): ?string
    {
        return $this->spanishContent;
    }

    public function setSpanishContent(?string $spanishContent): void
    {
        $this->spanishContent = $spanishContent;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function setIsPinned(bool $isPinned): void
    {
        $this->isPinned = $isPinned;
    }

    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    #[Groups(['news_read'])]
    public function getIsPublished(): bool
    {
        return $this->publicationDate < new \DateTime();
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    #[Groups(['news_read', 'news_write'])]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['news_read', 'news_write'])]
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
