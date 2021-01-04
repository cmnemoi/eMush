<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Room\Entity\Room;

/**
 * Class Door.
 *
 * @ORM\Entity()
 */
class Door extends GameEquipment
{
    /**
     * @ORM\ManyToMany(targetEntity="Mush\Room\Entity\Room")
     */
    private Collection $rooms;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();

        parent::__construct();
    }

    public function getActions(): Collection
    {
        return new ArrayCollection();
//        return new ArrayCollection([ActionEnum::MOVE, ActionEnum::REPAIR]);
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    /**
     * @return static
     */
    public function setRooms(Collection $rooms): Door
    {
        $this->rooms = $rooms;

        foreach ($rooms as $room) {
            if (!$room->getDoors()->contains($this)) {
                $room->addDoor($this);
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addRoom(Room $room): Door
    {
        $this->rooms->add($room);

        if (!$room->getDoors()->contains($this)) {
            $room->addDoor($this);
        }

        return $this;
    }

    public function getBrokenRate(): int
    {
        return 50;
    }
}
