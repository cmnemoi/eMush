<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'config_localization')]
class LocalizationConfig
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $timeZone;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $language;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): static
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }
}
