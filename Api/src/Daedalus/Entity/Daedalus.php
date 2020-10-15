<?php


namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Room\Entity\Room;

/**
 * Class Daedalus
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\Daedalus\Repository\DaedalusRepository")
 */
class Daedalus
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="daedalus")
     */
    private Collection $players;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Room\Entity\Room", mappedBy="daedalus")
     */
    private Collection $rooms;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $oxygen;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $fuel;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $hull;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycle;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $shield;

    /**
     * Daedalus constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function setPlayers(Collection $players): Daedalus
    {
        $this->players = $players;
        return $this;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function setRooms(Collection $rooms): Daedalus
    {
        $this->rooms = $rooms;
        return $this;
    }

    public function addRoom(Room $room): Daedalus
    {
        $this->rooms->add($room);
        $room->setDaedalus($this);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): Daedalus
    {
        $this->oxygen = $oxygen;
        return $this;
    }

    public function getFuel(): int
    {
        return $this->fuel;
    }

    public function setFuel(int $fuel): Daedalus
    {
        $this->fuel = $fuel;
        return $this;
    }

    public function getHull(): int
    {
        return $this->hull;
    }

    public function setHull(int $hull): Daedalus
    {
        $this->hull = $hull;
        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): Daedalus
    {
        $this->cycle = $cycle;
        return $this;
    }

    public function getShield(): int
    {
        return $this->shield;
    }

    public function setShield(int $shield): Daedalus
    {
        $this->shield = $shield;
        return $this;
    }
}