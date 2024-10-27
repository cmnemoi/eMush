<?php

namespace Mush\RoomLog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\TimestampableCancelInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;

#[ORM\Entity(repositoryClass: RoomLogRepository::class)]
class RoomLog implements TimestampableCancelInterface, SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $place = '';

    #[ORM\ManyToOne(targetEntity: PlayerInfo::class)]
    private ?PlayerInfo $playerInfo;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $visibility = '';

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $log = '';

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $parameters = [];

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $type = '';

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $day = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $cycle = 0;

    #[ORM\ManyToMany(targetEntity: Player::class)]
    #[ORM\JoinTable(name: 'room_log_readers')]
    private Collection $readers;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $timestampableCanceled = false;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => VisibilityEnum::HIDDEN])]
    private string $baseVisibility = VisibilityEnum::HIDDEN;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $noticed = false;

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

    public function isPublicOrRevealed(): bool
    {
        return $this->getVisibility() === VisibilityEnum::PUBLIC || $this->getVisibility() === VisibilityEnum::REVEALED;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function getMessage(): string
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

    public function getBaseVisibility(): string
    {
        return $this->baseVisibility;
    }

    public function setBaseVisibility(string $baseVisibility): self
    {
        $this->baseVisibility = $baseVisibility;

        return $this;
    }

    public function isUnnoticed(): bool
    {
        return $this->noticed === false;
    }

    public function markAsNoticed(): void
    {
        $this->noticed = true;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function hide(): void
    {
        $this->setVisibility(VisibilityEnum::HIDDEN);
    }

    public function shouldBeRevealedByCamera(): bool
    {
        return $this->isNotSabotageCameraLog() && $this->isNotRemoveCameraLog();
    }

    public function isCameraManipulationLog(): bool
    {
        return $this->log === ActionLogEnum::INSTALL_CAMERA || $this->log === ActionLogEnum::REMOVE_CAMERA;
    }

    public function isNotSabotageCameraLog(): bool
    {
        $isSabotageLog = \in_array($this->log, [ActionLogEnum::SABOTAGE_SUCCESS, ActionLogEnum::SABOTAGE_FAIL], true);
        $sabotagedEquipmentIsCamera = $this->getParameters()['target_equipment'] === EquipmentEnum::CAMERA_EQUIPMENT;

        return ($isSabotageLog && $sabotagedEquipmentIsCamera) === false;
    }

    public function resetVisibility(): void
    {
        $this->setVisibility($this->baseVisibility);
    }

    private function isNotRemoveCameraLog(): bool
    {
        return $this->log !== ActionLogEnum::REMOVE_CAMERA;
    }
}
