<?php

declare(strict_types=1);

namespace Mush\Game\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'config_localization')]
#[ApiResource(
    normalizationContext: ['groups' => ['localization_config_read']],
    denormalizationContext: ['groups' => ['localization_config_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(),
    ],
)]
#[UniqueEntity(fields: ['name'], errorPath: 'name')]
class LocalizationConfig
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['localization_config_read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    #[Groups(['localization_config_read', 'localization_config_write'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['localization_config_read', 'localization_config_write'])]
    private string $timeZone;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['localization_config_read', 'localization_config_write'])]
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
