<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Entity\Place;

/**
 * Class Door.
 *
 * @ORM\Entity()
 */
class Door extends GameEquipment
{
    /**
     * @ORM\ManyToMany(targetEntity="Mush\Place\Entity\Place")
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
    public function addRoom(Place $room): Door
    {
        $this->rooms->add($room);

        if (!$room->getDoors()->contains($this)) {
            $room->addDoor($this);
        }

        return $this;
    }

    public function getOtherRoom($currentRoom): Place
    {
        return $this->getRooms()->filter(fn (Place $room) => $room !== $currentRoom)->first();
    }
}
