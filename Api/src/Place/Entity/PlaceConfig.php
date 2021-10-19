<?php

namespace Mush\Place\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Place\Enum\PlaceTypeEnum;

/**
 * @ORM\Entity()
 */
class PlaceConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\DaedalusConfig", inversedBy="placeConfigs")
     */
    private DaedalusConfig $daedalusConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $type = PlaceTypeEnum::ROOM;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $doors = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $items = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $equipments = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }

    /**
     * @return static
     */
    public function setDaedalusConfig(DaedalusConfig $daedalusConfig): self
    {
        $this->daedalusConfig = $daedalusConfig;

        return $this;
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

    /**
     * @return static
     */
    public function setDoors(array $doors): self
    {
        $this->doors = $doors;

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

    public function getEquipments(): array
    {
        return $this->equipments;
    }

    /**
     * @return static
     */
    public function setEquipments(array $equipments): self
    {
        $this->equipments = $equipments;

        return $this;
    }
}
