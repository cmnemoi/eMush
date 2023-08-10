<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableCollection;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\HunterStatusEnum;

#[ORM\Entity]
#[ORM\Table(name: 'hunter')]
class Hunter implements GameVariableHolderInterface, LogParameterInterface, ModifierHolder, StatusHolderInterface
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

    #[ORM\OneToMany(mappedBy: 'hunter', targetEntity: GameModifier::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $modifiers;

    #[ORM\OneToMany(mappedBy: 'hunter', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\Column(type: 'string')]
    private string $target = HunterTargetEnum::DAEDALUS;

    #[ORM\Column(type: 'boolean')]
    private bool $inPool = false;

    public function __construct(HunterConfig $hunterConfig, Daedalus $daedalus)
    {
        $this->space = $daedalus->getSpace();
        $this->hunterConfig = $hunterConfig;
        $this->modifiers = new ArrayCollection();
        $this->statuses = new ArrayCollection();
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

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
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
        return get_class($this);
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

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getAllModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function addModifier(GameModifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
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
        return !$this->hasStatus(HunterStatusEnum::HUNTER_CHARGE);
    }
}
