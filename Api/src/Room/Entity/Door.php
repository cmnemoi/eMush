<?php


namespace Mush\Room\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class Door
 * @package Mush\Entity
 *
 * @ORM\Entity()
 */
class Door
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $statuses;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Room\Entity\Room")
     */
    private Collection $rooms;

    /**
     * Door constructor.
     */
    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Door
    {
        $this->name = $name;
        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): Door
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function setRooms(Collection $rooms): Door
    {
        $this->rooms = $rooms;
        return $this;
    }

    public function addRoom(Room $room): Door
    {
        $this->rooms->add($room);
        return $this;
    }
}