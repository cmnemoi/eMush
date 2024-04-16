<?php

namespace Mush\RoomLog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\TimestampableCancelInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Repository\RoomLogRepository;

#[ORM\Entity(repositoryClass: RoomLogRepository::class)]
class RoomLog implements TimestampableCancelInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $place;

    #[ORM\ManyToOne(targetEntity: PlayerInfo::class)]
    private ?PlayerInfo $playerInfo;

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

    #[ORM\ManyToMany(targetEntity: Player::class)]
    #[ORM\JoinTable(name: 'room_log_readers')]
    private Collection $readers;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $timestampableCanceled = false;

    public function __construct()
    {
        $this->readers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function setPlace(string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getPlayerInfo(): ?PlayerInfo
    {
        return $this->playerInfo;
    }

    public function setPlayerInfo(?PlayerInfo $playerInfo): static
    {
        $this->playerInfo = $playerInfo;

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

    public function addReader(Player $reader): static
    {
        if (!$this->readers->contains($reader)) {
            $this->readers->add($reader);
        }

        return $this;
    }

    public function isUnreadBy(Player $player): bool
    {
        return !$this->readers->contains($player);
    }

    public function isReadBy(Player $player): bool
    {
        return $this->readers->contains($player);
    }

    public function isTimestampableCanceled(): bool
    {
        return $this->timestampableCanceled;
    }

    public function cancelTimestampable(): void
    {
        $this->timestampableCanceled = true;
    }
}
