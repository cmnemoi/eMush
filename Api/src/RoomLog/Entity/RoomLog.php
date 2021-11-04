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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $visibility;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $log;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $parameters;

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
    public function setPlace(Place $place): self
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
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return static
     */
    public function setVisibility(string $visibility): self
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
    public function setLog(string $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return static
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

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

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return static
     */
    public function setDate(\DateTime $date): self
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
    public function setDay(int $day): self
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
    public function setCycle(int $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }
}
