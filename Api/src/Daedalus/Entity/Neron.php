<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class Neron
{
    public const CRAZY_NERON_CHANCE = 25;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'neron', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isInhibited = true;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => NeronCpuPriorityEnum::NONE])]
    private string $cpuPriority = NeronCpuPriorityEnum::NONE;

    #[ORM\Column(type: 'string', enumType: NeronCrewLockEnum::class, nullable: false, options: ['default' => NeronCrewLockEnum::PILOTING])]
    private NeronCrewLockEnum $crewLock = NeronCrewLockEnum::PILOTING;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isPlasmaShieldActive = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $isMagneticNetActive = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $vocodedAnnouncements = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $deathAnnouncements = true;

    #[ORM\Column(type: 'string', enumType: NeronFoodDestructionEnum::class, nullable: false, options: ['default' => NeronFoodDestructionEnum::UNSTABLE])]
    private NeronFoodDestructionEnum $foodDestructionOption = NeronFoodDestructionEnum::UNSTABLE;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): self
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function setIsInhibited(bool $isInhibited): self
    {
        $this->isInhibited = $isInhibited;

        return $this;
    }

    public function isInhibited(): bool
    {
        return $this->isInhibited;
    }

    public function toggleInhibition(): void
    {
        $this->isInhibited = !$this->isInhibited;
    }

    public function setCpuPriority(string $cpuPriority): self
    {
        $this->cpuPriority = $cpuPriority;

        return $this;
    }

    public function getCpuPriority(): string
    {
        return $this->cpuPriority;
    }

    public function getCrewLock(): NeronCrewLockEnum
    {
        return $this->crewLock;
    }

    public function changeCrewLockTo(NeronCrewLockEnum $newCrewLock): void
    {
        $this->crewLock = $newCrewLock;
    }

    public function isPlasmaShieldActive(): bool
    {
        return $this->isPlasmaShieldActive;
    }

    public function togglePlasmaShield(): void
    {
        $this->isPlasmaShieldActive = !$this->isPlasmaShieldActive;
    }

    public function isMagneticNetActive(): bool
    {
        return $this->isMagneticNetActive;
    }

    public function toggleMagneticNet(): void
    {
        $this->isMagneticNetActive = !$this->isMagneticNetActive;
    }

    public function areVocodedAnnouncementsActive(): bool
    {
        return $this->vocodedAnnouncements;
    }

    public function toggleVocodedAnnouncements(): void
    {
        $this->vocodedAnnouncements = !$this->vocodedAnnouncements;
    }

    public function shouldRefuseVocodedAnnouncementsForPlayer(Player $player): bool
    {
        return $player->doesNotHaveTitle(TitleEnum::NERON_MANAGER) || $this->vocodedAnnouncements === false;
    }

    public function areDeathAnnouncementsActive(): bool
    {
        return $this->deathAnnouncements;
    }

    public function toggleDeathAnnouncements(): void
    {
        $this->deathAnnouncements = !$this->deathAnnouncements;
    }

    public function getFoodDestructionOption(): NeronFoodDestructionEnum
    {
        return $this->foodDestructionOption;
    }

    public function changeFoodDestructionOption(NeronFoodDestructionEnum $newFoodOption): void
    {
        $this->foodDestructionOption = $newFoodOption;
    }
}
