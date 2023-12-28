<?php

namespace Mush\MetaGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'news')]
class News
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $frenchTitle;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $frenchContent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $englishTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $englishContent = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $spanishTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $spanishContent = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isPinned = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $publicationDate = null;

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

    public function getEnglishTitle(): string|null
    {
        return $this->englishTitle;
    }

    public function setEnglishTitle(?string $englishTitle): void
    {
        $this->englishTitle = $englishTitle;
    }

    public function getEnglishContent(): string|null
    {
        return $this->englishContent;
    }

    public function setEnglishContent(?string $englishContent): void
    {
        $this->englishContent = $englishContent;
    }

    public function getSpanishTitle(): string|null
    {
        return $this->spanishTitle;
    }

    public function setSpanishTitle(?string $spanishTitle): void
    {
        $this->spanishTitle = $spanishTitle;
    }

    public function getSpanishContent(): string|null
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

    public function getPublicationDate(): \DateTime|null
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getIsPublished(): bool
    {
        return $this->publicationDate < new \DateTime();
    }
}
