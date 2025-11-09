<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionHolderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Entity\VisibleStatusHolderInterface;
use Mush\Status\Enum\HunterStatusEnum;

#[ORM\Entity]
#[ORM\Table(name: 'hunter')]
class Hunter implements GameVariableHolderInterface, LogParameterInterface, StatusHolderInterface, ActionHolderInterface, HunterTargetEntityInterface, VisibleStatusHolderInterface
{
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: HunterConfig::class)]
    private HunterConfig $hunterConfig;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'hunters')]
    private Place $space;

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private HunterVariables $hunterVariables;

    #[ORM\OneToMany(mappedBy: 'hunter', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\OneToOne(targetEntity: HunterTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private ?HunterTarget $target;

    #[ORM\Column(type: 'boolean')]
    private bool $inPool = false;

    public function __construct(HunterConfig $hunterConfig, Daedalus $daedalus)
    {
        $this->space = $daedalus->getSpace();
        $this->hunterConfig = $hunterConfig;
        $this->statuses = new ArrayCollection();
        $this->target = null;

        $this->setHunterVariables($hunterConfig);
        $this->space->addHunter($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHunterConfig(): HunterConfig
    {
        return $this->hunterConfig;
    }

    public function setHunterConfig(HunterConfig $hunterConfig): self
    {
        $this->hunterConfig = $hunterConfig;

        return $this;
    }

    public function getSpace(): Place
    {
        return $this->space;
    }

    public function setSpace(Place $space): self
    {
        $this->space = $space;

        return $this;
    }

    public function getTarget(): ?HunterTarget
    {
        return $this->target;
    }

    public function getTargetOrThrow(): HunterTarget
    {
        if ($this->target === null) {
            throw new \RuntimeException('Hunter has no target');
        }

        return $this->target;
    }

    public function getTargetEntityOrThrow(): HunterTargetEntityInterface
    {
        if ($this->target === null) {
            throw new \RuntimeException('Hunter has no target');
        }

        return $this->target->getTargetEntity();
    }

    public function setTarget(HunterTarget $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function isTargetInBattle(): bool
    {
        return $this->getTargetEntityOrThrow()->isInSpaceBattle();
    }

    public function isInPool(): bool
    {
        return $this->inPool;
    }

    public function putInPool(): self
    {
        $this->inPool = true;

        return $this;
    }

    public function unpool(): self
    {
        $this->inPool = false;

        return $this;
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->hunterVariables->getVariableByName($variableName);
    }

    public function getVariableValueByName(string $variableName): int
    {
        return $this->hunterVariables->getValueByName($variableName);
    }

    public function setVariableValueByName(int $value, string $variableName): static
    {
        $this->hunterVariables->setValueByName($value, $variableName);

        return $this;
    }

    public function changeVariableValueByName(int $delta, string $variableName): static
    {
        $this->hunterVariables->changeValueByName($delta, $variableName);

        return $this;
    }

    public function getGameVariables(): HunterVariables
    {
        return $this->hunterVariables;
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->hunterVariables->hasVariable($variableName);
    }

    public function setHunterVariables(HunterConfig $hunterConfig): static
    {
        $this->hunterVariables = new HunterVariables($hunterConfig);

        return $this;
    }

    public function getHealth(): int
    {
        return $this->getVariableValueByName(HunterVariableEnum::HEALTH);
    }

    public function setHealth(int $health): static
    {
        $this->setVariableValueByName($health, HunterVariableEnum::HEALTH);

        return $this;
    }

    public function getHitChance(): int
    {
        return $this->getVariableValueByName(HunterVariableEnum::HIT_CHANCE);
    }

    public function setHitChance(int $hitChance): static
    {
        $this->setVariableValueByName($hitChance, HunterVariableEnum::HIT_CHANCE);

        return $this;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setHunter($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->space->getDaedalus();
    }

    public function getName(): string
    {
        return $this->getHunterConfig()->getHunterName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::HUNTER;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getGameEquipment(): null
    {
        return null;
    }

    public function getPlace(): Place
    {
        return $this->space;
    }

    public function getPlayer(): null
    {
        return null;
    }

    public function canShoot(): bool
    {
        return !$this->hasStatus(HunterStatusEnum::TRUCE_CYCLES) && !$this->hasStatus(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
    }

    public function hasSelectedATarget(): bool
    {
        return $this->target !== null;
    }

    public function resetTarget(): static
    {
        $this->target?->reset();
        $this->target = null;

        return $this;
    }

    public function getActions(Player $activePlayer, ?ActionHolderEnum $actionTarget = null): Collection
    {
        return $activePlayer->getPlace()->getProvidedActions(ActionHolderEnum::HUNTER, [ActionRangeEnum::ROOM, ActionRangeEnum::SHELF]);
    }

    public function isNotAnAsteroid(): bool
    {
        return $this->getHunterConfig()->getHunterName() !== HunterEnum::ASTEROID;
    }

    public function getDrawCost(): int
    {
        return $this->getHunterConfig()->getDrawCost();
    }

    public function getMaxPerWave(): ?int
    {
        return $this->getHunterConfig()->getMaxPerWave();
    }

    public function getBonusAfterFailedShot(): int
    {
        return $this->getHunterConfig()->getBonusAfterFailedShot();
    }

    public function getDamageRange(): ProbaCollection
    {
        return $this->getHunterConfig()->getDamageRange();
    }

    public function aimAtDaedalus(): void
    {
        $this->setTarget(new HunterTarget($this));
    }

    public function isScrambled(D100RollServiceInterface $d100Roll): bool
    {
        $meridonScrambler = $this->getDaedalus()->getProjectByName(ProjectName::MERIDON_SCRAMBLER);

        return $this->isSimpleHunter() && $meridonScrambler->isFinished() && $d100Roll->isSuccessful($meridonScrambler->getActivationRate());
    }

    public function isInAPatrolShip(): false
    {
        return false;
    }

    public function isInSpace(): false
    {
        return false;
    }

    public function isInSpaceBattle(): bool
    {
        return $this->isInPool() === false;
    }

    public function hasNoHealth(): bool
    {
        return $this->getHealth() <= 0;
    }

    public function isTransport(): bool
    {
        return $this->getHunterConfig()->getHunterName() === HunterEnum::TRANSPORT;
    }

    public function isNonHostile(): bool
    {
        return HunterEnum::getNonHostiles()->contains($this->getHunterConfig()->getHunterName());
    }

    private function isSimpleHunter(): bool
    {
        return $this->getHunterConfig()->getHunterName() === HunterEnum::HUNTER;
    }
}
