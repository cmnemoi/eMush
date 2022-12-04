<?php

namespace Mush\Place\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Enum\PlaceTypeEnum;

/**
 * @ORM\Entity()
 */
#[ORM\Entity]
class PlaceConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type = PlaceTypeEnum::ROOM;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $doors = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $items = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $equipments = [];

    public function getId(): int
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDoors(): array
    {
        return $this->doors;
    }

    public function setDoors(array $doors): static
    {
        $this->doors = $doors;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getEquipments(): array
    {
        return $this->equipments;
    }

    public function setEquipments(array $equipments): static
    {
        $this->equipments = $equipments;

        return $this;
    }
}
