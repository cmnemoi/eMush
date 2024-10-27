<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionHolderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Exception\ProgressShouldBePositive;
use Mush\Project\Factory\ProjectFactory;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;

#[ORM\Entity]
class Project implements LogParameterInterface, ActionHolderInterface, ModifierProviderInterface
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

    #[ORM\Column(type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $lastParticipationTime;

    public function __construct(ProjectConfig $config, Daedalus $daedalus)
    {
        $this->config = $config;
        $this->daedalus = $daedalus;

        $this->daedalus->addProject($this);

        if ($config->getType() !== ProjectType::NERON_PROJECT) {
            $this->proposed = true;
        }

        $this->lastParticipationTime = new \DateTime();
    }

    public static function createNull(): self
    {
        return ProjectFactory::createNullProject();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRequirements()
    {
        return $this->config->getRequirements();
    }

    public function getConfig(): ProjectConfig
    {
        return $this->config;
    }

    public function isVisibleFor(Player $player): bool
    {
        $requirements = $this->config->getRequirements();
        foreach ($requirements as $requirement) {
            if (!$requirement->isSatisfiedFor($player)) {
                return false;
            }
        }

        return true;
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

    /**
     * @return Collection<int, SpawnEquipmentConfig>
     */
    public function getSpawnEquipmentConfigs(): Collection
    {
        return $this->config->getSpawnEquipmentConfigs();
    }

    /**
     * @return Collection<int, ReplaceEquipmentConfig>
     */
    public function getReplaceEquipmentConfigs(): Collection
    {
        return $this->config->getReplaceEquipmentConfigs();
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function hasBeenAdvanced(): bool
    {
        return $this->progress > 0;
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

    public function makeProgressAndUpdateParticipationDate(int $progress): void
    {
        $this->makeProgress($progress);

        $this->lastParticipationTime = new \DateTime();
    }

    public function revertProgress(int $progress): void
    {
        if ($progress < 0) {
            throw new ProgressShouldBePositive($progress);
        }

        $this->progress -= $progress;
    }

    public function finish(): void
    {
        $this->progress = 100;
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

    public function isFinished(): bool
    {
        return $this->progress >= 100;
    }

    public function isNotFinished(): bool
    {
        return $this->progress < 100;
    }

    public function isNeronProject(): bool
    {
        return $this->getType() === ProjectType::NERON_PROJECT;
    }

    public function isResearchProject(): bool
    {
        return $this->getType() === ProjectType::RESEARCH;
    }

    public function isPilgred(): bool
    {
        return $this->getType() === ProjectType::PILGRED;
    }

    public function isNotPilgred(): bool
    {
        return $this->getType() !== ProjectType::PILGRED;
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

    public function getActions(Player $activePlayer, ?ActionHolderEnum $actionTarget = null): Collection
    {
        return $activePlayer->getPlace()->getProvidedActions(ActionHolderEnum::PROJECT, [ActionRangeEnum::ROOM, ActionRangeEnum::SHELF]);
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

    public function getLastParticipationTime(): \DateTime
    {
        return $this->lastParticipationTime;
    }

    public function isLastProjectAdvanced(): bool
    {
        $lastAdvancedProject = match ($this->getType()) {
            ProjectType::NERON_PROJECT => $this->daedalus->getAdvancedNeronProjects()->getLastAdvancedProject(),
            ProjectType::RESEARCH => $this->daedalus->getAdvancedResearchProjects()->getLastAdvancedProject(),
            default => self::createNull(),
        };

        return $this->equals($lastAdvancedProject);
    }

    public function notEquals(self $project): bool
    {
        return $this->equals($project) === false;
    }

    public function isNull(): bool
    {
        return $this->equals(self::createNull());
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        return null;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        if ($this->progress !== 100) {
            return ActionProviderOperationalStateEnum::UNFINISHED;
        }

        if ($this->daedalus->hasActiveProject($this->getConfig()->getName())) {
            return ActionProviderOperationalStateEnum::OPERATIONAL;
        }

        return ActionProviderOperationalStateEnum::DEACTIVATED;
    }

    public function getAllModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->config->getModifierConfigs()->toArray());
    }

    private function equals(self $project): bool
    {
        return $this->id === $project->getId();
    }
}
