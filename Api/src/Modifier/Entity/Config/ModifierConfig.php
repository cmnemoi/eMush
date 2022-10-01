<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_config')]
class ModifierConfig
{

    public const EVERY_REASONS = 'every_reasons';
    public const EXCLUDE = 'exclude';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToMany(targetEntity: ModifierCondition::class)]
    private Collection $conditions;

    private string $reach;

    private string $name;

    private float $value;

    private string $mode;

    private ?string $logKeyWhenApplied;

    private array $targetEvents;

    private ?string $playerVariable;

    public function __construct(string $name, string $reach, float $value, string $mode, string $playerVariable = null)
    {
        $this->name = $name;
        $this->reach = $reach;
        $this->value = $value;
        $this->mode = $mode;
        $this->playerVariable = $playerVariable;

        $this->logKeyWhenApplied = null;
        $this->conditions = new ArrayCollection();
        $this->targetEvents = [];
    }

    public function addTargetEventsWithExcludedReasons(string $eventName, array $eventReason) : self {
        $excluded = array_merge($eventReason, [self::EXCLUDE]);
        $this->addTargetEvent($eventName, $excluded);
    }

    public function addTargetEvent(string $eventName, array $eventReason = null) : self
    {
        $events =
            $eventReason === null ?
                [$eventName => self::EVERY_REASONS] :
                [$eventName => $eventReason];
        $this->targetEvents = array_merge($this->targetEvents, $events);
        return $this;
    }

    public function isTargetedBy(string $eventName, string $eventReason) : bool
    {
        $reasons = $this->targetEvents[$eventName];
        if (isset($reasons)) {
            if (in_array(self::EXCLUDE, $reasons)) {
                return !in_array($eventReason, $reasons);
            }

            return  ($reasons === self::EVERY_REASONS || in_array($eventReason, $reasons));
        }

        return false;
    }

    public function areConditionsTrue(ModifierHolder $holder, RandomServiceInterface $randomService) : bool {
        /* @var ModifierCondition $condition */
        foreach ($this->getConditions()->toArray() as $condition) {
            if (!$condition->isTrue($holder, $randomService)) {
                return false;
            }
        }

        return true;
    }

    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(ModifierCondition $condition) : self {
        $this->conditions->add($condition);
        return $this;
    }

    public function getTargetEvents(): array
    {
        return $this->targetEvents;
    }

    public function getPlayerVariable(): ?string
    {
        return $this->playerVariable;
    }

    public function getReach(): string
    {
        return $this->reach;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setLogKeyWhenApplied(?string $logKeyWhenApplied): void
    {
        $this->logKeyWhenApplied = $logKeyWhenApplied;
    }

    public function getLogKeyWhenApplied(): ?string
    {
        return $this->logKeyWhenApplied;
    }

}
