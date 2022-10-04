<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\ModifierHolder;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_config')]
class ModifierConfig
{
    public const EVERY_REASONS = 'every_reasons';
    public const EXCLUDE_REASON = 'exclude_reason';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToMany(targetEntity: ModifierCondition::class)]
    private Collection $conditions;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $reach;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'float', nullable: false)]
    private float $value;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $logKeyWhenApplied;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $targetEvents;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $variable;

    public function __construct(string $name, string $reach, float $value, string $mode, string $variable = null)
    {
        $this->name = $name;
        $this->reach = $reach;
        $this->value = $value;
        $this->mode = $mode;
        $this->variable = $variable;

        $this->logKeyWhenApplied = null;
        $this->conditions = new ArrayCollection();
        $this->targetEvents = [];
    }

    public function addTargetEvent(string $eventName, array $eventReason = null): self
    {
        if (array_key_exists($eventName, $this->targetEvents)) {
            if ($eventReason === null) {
                $this->targetEvents[$eventName] = [[self::EVERY_REASONS]];
            } else {
                $this->targetEvents[$eventName] = array_merge($this->targetEvents[$eventName], [$eventReason]);
            }
        } else {
            if ($eventReason === null) {
                $this->targetEvents = array_merge($this->targetEvents, [$eventName => [[self::EVERY_REASONS]]]);
            } else {
                $this->targetEvents = array_merge($this->targetEvents, [$eventName => [$eventReason]]);
            }
        }

        return $this;
    }

    public function excludeTargetEvent(string $eventName, array $eventReason): self
    {
        if (isset($this->targetEvents[$eventName])) {
            $this->targetEvents[$eventName] = array_merge($this->targetEvents[$eventName], [$eventReason]);
        } else {
            $this->targetEvents = array_merge($this->targetEvents, [$eventName => [array_merge([self::EXCLUDE_REASON], $eventReason)]]);
        }

        return $this;
    }

    public function isTargetedBy(string $eventName, array $eventReasons): bool
    {
        if (!array_key_exists($eventName, $this->targetEvents)) {
            return false;
        }

        $reasons = $this->targetEvents[$eventName];
        codecept_debug($reasons);

        for ($i = 0; $i < count($reasons); ++$i) {
            if (in_array(self::EVERY_REASONS, $reasons[$i])) {
                return true;
            }

            if (in_array(self::EXCLUDE_REASON, $reasons[$i])) {
                if ($this->isTargetReasonsInOrder(array_splice($reasons, 1), $eventReasons)) {
                    return false;
                }
            }

            if ($this->isTargetReasonsInOrder($reasons[$i], $eventReasons)) {
                return true;
            }
        }

        return false;
    }

    private function isTargetReasonsInOrder(array $reasons, array $eventReasons): bool
    {
        for ($i = 0; $i < count($reasons); ++$i) {
            if ($reasons[$i] !== $eventReasons[$i]) {
                return false;
            }
        }

        return true;
    }

    public function areConditionsTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
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

    public function addCondition(ModifierCondition $condition): self
    {
        $this->conditions->add($condition);

        return $this;
    }

    public function getTargetEvents(): array
    {
        return $this->targetEvents;
    }

    public function getVariable(): ?string
    {
        return $this->variable;
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
