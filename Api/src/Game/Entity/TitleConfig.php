<?php

declare(strict_types=1);

namespace Mush\Game\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['title_config_read']],
    denormalizationContext: ['groups' => ['title_config_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
#[UniqueEntity(fields: ['name'], errorPath: 'name')]
#[ORM\Entity]
#[ORM\Table(name: 'title_config')]
class TitleConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['title_config_read'])]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[Groups(['title_config_read', 'title_config_write'])]
    private string $name;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['title_config_read', 'title_config_write'])]
    private array $priority;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPriority(): array
    {
        return $this->priority;
    }

    public function setPriority(array $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
