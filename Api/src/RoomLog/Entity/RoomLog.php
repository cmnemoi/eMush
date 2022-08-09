<?php

namespace Mush\RoomLog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Repository\RoomLogRepository;

#[ORM\Entity(repositoryClass: RoomLogRepository::class)]
class RoomLog
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private Place $place;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $visibility;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $log;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $parameters;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $date;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $day;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycle;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
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

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }
}
