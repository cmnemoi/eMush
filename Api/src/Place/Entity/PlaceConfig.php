<?php

namespace Mush\Place\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\MetaGame\Entity\Skin\SkinableConfigInterface;
use Mush\MetaGame\Entity\Skin\SkinSlotConfig;
use Mush\Place\Enum\PlaceTypeEnum;

/**
 * @ORM\Entity()
 */
#[ORM\Entity]
class PlaceConfig implements SkinableConfigInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $placeName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type = PlaceTypeEnum::ROOM;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $doors = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $items = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $equipments = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $patrolShipNames = [];

    #[ORM\ManyToMany(targetEntity: SkinSlotConfig::class, cascade: ['REMOVE'], orphanRemoval: true)]
    private Collection $skinSlotsConfig;

    public function __construct()
    {
        $this->skinSlotsConfig = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function setPlaceName(string $placeName): static
    {
        $this->placeName = $placeName;

        return $this;
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

    public function buildName(string $configName): static
    {
        $this->name = $this->placeName . '_' . $configName;

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

    public function getPatrolShipNames(): array
    {
        return $this->patrolShipNames;
    }

    public function setPatrolShipNames(array $patrolShipNames): static
    {
        $this->patrolShipNames = $patrolShipNames;

        return $this;
    }

    public function getSkinSlotsConfig(): ArrayCollection
    {
        return new ArrayCollection($this->skinSlotsConfig->toArray());
    }

    public function addSkinSlot(SkinSlotConfig $skinSlotConfig): static
    {
        $this->skinSlotsConfig->add($skinSlotConfig);

        return $this;
    }

    public function setSkinSlotsConfig(ArrayCollection $skinSlotsConfig): static
    {
        $this->skinSlotsConfig = $skinSlotsConfig;

        return $this;
    }
}
