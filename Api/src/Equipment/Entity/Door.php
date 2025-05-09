<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\DoorEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class Door extends GameEquipment
{
    #[ORM\ManyToMany(targetEntity: Place::class)]
    private Collection $rooms;

    public function __construct(EquipmentHolderInterface $equipmentHolder)
    {
        $this->rooms = new ArrayCollection();

        parent::__construct($equipmentHolder);
    }

    public static function createFromRooms(Place $room1, Place $room2): self
    {
        $door = new self($room1);
        $door
            ->addRoom($room1)
            ->addRoom($room2)
            ->setName($door->buildName());

        return $door;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function setRooms(Collection $rooms): self
    {
        $this->rooms = $rooms;

        foreach ($rooms as $room) {
            if (!$room->getDoors()->contains($this)) {
                $room->addDoor($this);
            }
        }

        return $this;
    }

    public function addRoom(Place $room): self
    {
        $this->rooms->add($room);

        if (!$room->getDoors()->contains($this)) {
            $room->addDoor($this);
        }

        return $this;
    }

    public function getOtherRoom(Place $currentRoom): Place
    {
        return $this->getRooms()->filter(static fn (Place $room) => $room !== $currentRoom)->first();
    }

    public function getLogName(): string
    {
        return EquipmentEnum::DOOR;
    }

    public function canPlayerReach(Player $player): bool
    {
        return $this->getRooms()->contains($player->getPlace());
    }

    public function getNormalizationType(): string
    {
        return LogParameterKeyEnum::DOOR;
    }

    public function shouldTriggerRoomTrap(): bool
    {
        return false;
    }

    public function isBreakable(): bool
    {
        return DoorEnum::isBreakable($this->getName());
    }

    private function buildName(): string
    {
        return implode('_', $this->getRooms()->map(static fn (Place $room) => $room->getName())->toArray());
    }
}
