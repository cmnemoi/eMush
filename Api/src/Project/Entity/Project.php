<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionTargetInterface;
use Mush\Action\Enum\ActionTargetName;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Exception\ProgressShouldBePositive;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class Project implements LogParameterInterface, ActionTargetInterface
{
    public const int CPU_PRIORITY_BONUS = 1;
    public const int PARTICIPATION_MALUS = 2;
    public const int SKILL_BONUS = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProjectConfig::class)]
    private ProjectConfig $config;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 0])]
    private int $progress = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $available = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $proposed = false;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'projects')]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $lastParticipant = null;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $lastParticipantNumberOfParticipations = 0;

    public function __construct(ProjectConfig $config, Daedalus $daedalus)
    {
        $this->config = $config;
        $this->daedalus = $daedalus;

        $this->daedalus->addProject($this);

        if ($config->getType() !== ProjectType::NERON_PROJECT) {
            $this->proposed = true;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->config->getName()->value;
    }

    public function getType(): ProjectType
    {
        return $this->config->getType();
    }

    public function getEfficiency(): int
    {
        return $this->config->getEfficiency();
    }

    public function getBonusSkills(): array
    {
        return $this->config->getBonusSkills();
    }

    public function getActivationRate(): int
    {
        return $this->config->getActivationRate();
    }

    public function getSpawnEquipmentConfigs(): Collection
    {
        return $this->config->getSpawnEquipmentConfigs();
    }

    /**
     * @return Collection<ReplaceEquipmentConfig>
     */
    public function getReplaceEquipmentConfigs(): Collection
    {
        return $this->config->getReplaceEquipmentConfigs();
    }

    public function getModifierConfigs(): Collection
    {
        return $this->config->getModifierConfigs();
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function isProposed(): bool
    {
        return $this->proposed;
    }

    public function propose(): void
    {
        $this->proposed = true;
        $this->available = false;
    }

    public function unpropose(): void
    {
        $this->proposed = false;
        $this->available = false;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function makeProgress(int $progress): void
    {
        if ($progress < 0) {
            throw new ProgressShouldBePositive($progress);
        }

        $this->progress += $progress;
        if ($this->progress > 100) {
            $this->progress = 100;
        }
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::PROJECT;
    }

    public function getActionTargetName(array $context): string
    {
        return ActionTargetName::PROJECT->value;
    }

    public function isFinished(): bool
    {
        return $this->progress >= 100;
    }

    public function isNeronProject(): bool
    {
        return $this->getType() === ProjectType::NERON_PROJECT;
    }

    public function isPilgred(): bool
    {
        return $this->getType() === ProjectType::PILGRED;
    }

    public function isAvailableNeronProject(): bool
    {
        return $this->isNeronProject() && $this->isAvailable();
    }

    public function isProposedNeronProject(): bool
    {
        return $this->isNeronProject() && $this->isProposed();
    }

    public function isFinishedNeronProject(): bool
    {
        return $this->isNeronProject() && $this->isFinished();
    }

    public function getPlayerParticipations(Player $player): int
    {
        if ($this->lastParticipant?->getId() !== $player->getId()) {
            return 0;
        }

        return $this->lastParticipantNumberOfParticipations;
    }

    public function addPlayerParticipation(Player $player): void
    {
        if ($this->lastParticipant?->getId() !== $player->getId()) {
            $this->lastParticipant = $player;
            $this->lastParticipantNumberOfParticipations = 1;
        } else {
            ++$this->lastParticipantNumberOfParticipations;
        }
    }

    public function resetPlayerParticipations(Player $player): void
    {
        if ($this->lastParticipant?->getId() === $player->getId()) {
            $this->lastParticipant = null;
            $this->lastParticipantNumberOfParticipations = 0;
        }
    }
}
