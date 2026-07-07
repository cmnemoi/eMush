<?php

declare(strict_types=1);

namespace Mush\Daedalus\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("ROLE_ADMIN")', filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['random_item_place_read']],
    denormalizationContext: ['groups' => ['random_item_place_write']],
    paginationItemsPerPage: 25,
)]
#[ApiResource(
    uriTemplate: '/daedalus_configs/{daedalusConfigId}/random_item_places',
    operations: [new GetCollection()],
    uriVariables: [
        'daedalusConfigId' => new Link(fromProperty: 'randomItemPlaces', fromClass: DaedalusConfig::class),
    ],
    normalizationContext: ['groups' => ['random_item_place_read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
#[ORM\Entity]
class RandomItemPlaces
{
    #[Groups(['random_item_place_read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[Groups(['random_item_place_read', 'random_item_place_write'])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[Groups(['random_item_place_read', 'random_item_place_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $places;

    #[Groups(['random_item_place_read', 'random_item_place_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $items;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    /**
     * @return static
     */
    public function setPlaces(array $places): self
    {
        $this->places = $places;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return static
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }
}
