<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class storing the various information needed to spawn/replace a GameEquipment.
 *
 * name: a unique name needed for the DB
 * equipmentName: the name of the GameEquipment spawned by the event
 * roomName: the name of the Room where the GameEquipment is spawned.
 * quantity: the amount of GameEquipment spawned.
 * replacedEquipment: if specified, the spawn event will replace the specified equipment from the room.
 */
#[ORM\Entity]
class SpawnEquipmentEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $equipmentName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $roomName;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $replacedEquipment;

    public function __construct(
        string $name,
        string $eventName,
        string $equipmentName,
        string $roomName,
        int $quantity = 1,
        string $replacedEquipment = null,
    ) {
        $this->name = $name;
        $this->eventName = $eventName;
        $this->equipmentName = $equipmentName;
        $this->roomName = $roomName;
        $this->quantity = $quantity;
        $this->replacedEquipment = $replacedEquipment;
    }

    public function buildName(): static
    {
        $this->name = $this->eventName . '_' . $this->quantity . '_' . $this->equipmentName . '_in_' . $this->roomName;

        return $this;
    }

    public function getEquipmentName(): string
    {
        return $this->equipmentName;
    }

    public function setEquipmentName(string $equipmentName): self
    {
        $this->equipmentName = $equipmentName;

        return $this;
    }

    public function getRoomName(): string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): self
    {
        $this->roomName = $roomName;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getReplacedEquipment(): ?string
    {
        return $this->replacedEquipment;
    }

    public function setReplacedEquipment(?string $replacedEquipment): void
    {
        $this->replacedEquipment = $replacedEquipment;
    }

    public function getTranslationParameters(): array
    {
        return [
            'equipmentName' => $this->equipmentName,
            'roomName' => $this->roomName,
            'quantity' => abs($this->quantity),
        ];
    }

    public function updateFromConfigData(array $configData): void
    {
        $this->name = $configData['name'];
        $this->eventName = $configData['eventName'];
        $this->equipmentName = $configData['equipmentName'];
        $this->roomName = $configData['roomName'];
        $this->quantity = $configData['quantity'];
        $this->replacedEquipment = array_key_exists('replacedEquipment', $configData) ?
            $configData['replacedEquipment'] : null;
    }
}
