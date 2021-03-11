<?php

namespace Mush\RoomLog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

/**
 * Class RoomLog.
 *
 * @ORM\Entity(repositoryClass="Mush\RoomLog\Repository\RoomLogRepository")
 */
class RoomLog
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Place\Entity\Place")
     */
    private Place $place;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player")
     */
    private ?Player $player;

    /**
     * @ORM\OneToOne  (targetEntity="Mush\RoomLog\Entity\Target", cascade={"All"}, orphanRemoval=true)
     */
    private ?Target $target;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $visibility;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $log;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $type;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTime $date;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $day;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycle;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * @return static
     */
    public function setPlace(Place $place): RoomLog
    {
        $this->place = $place;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @return static
     */
    public function setPlayer(?Player $player): RoomLog
    {
        $this->player = $player;

        return $this;
    }

    public function getTarget(): ?Target
    {
        return $this->target;
    }

    /**
     * @return static
     */
    public function setTarget(?Target $target): RoomLog
    {
        $this->target = $target;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return static
     */
    public function setVisibility(string $visibility): RoomLog
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * @return static
     */
    public function setLog(string $log): RoomLog
    {
        $this->log = $log;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): RoomLog
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return static
     */
    public function setDate(\DateTime $date): RoomLog
    {
        $this->date = $date;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @return static
     */
    public function setDay(int $day): RoomLog
    {
        $this->day = $day;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    /**
     * @return static
     */
    public function setCycle(int $cycle): RoomLog
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @return static
     */
    public function setQuantity(?int $quantity): RoomLog
    {
        $this->quantity = $quantity;

        return $this;
    }
}
