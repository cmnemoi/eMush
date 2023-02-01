<?php

namespace Mush\MetaGame\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'news')]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $updatedAt;

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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
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

    public function getEnglishTitle(): string
    {
        return $this->englishTitle;
    }

    public function setEnglishTitle(string $englishTitle): void
    {
        $this->englishTitle = $englishTitle;
    }

    public function getEnglishContent(): string
    {
        return $this->englishContent;
    }

    public function setEnglishContent(string $englishContent): void
    {
        $this->englishContent = $englishContent;
    }

    public function getSpanishTitle(): string
    {
        return $this->spanishTitle;
    }

    public function setSpanishTitle(string $spanishTitle): void
    {
        $this->spanishTitle = $spanishTitle;
    }

    public function getSpanishContent(): string
    {
        return $this->spanishContent;
    }

    public function setSpanishContent(string $spanishContent): void
    {
        $this->spanishContent = $spanishContent;
    }
}
