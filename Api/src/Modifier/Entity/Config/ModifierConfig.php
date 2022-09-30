<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierConditionCollection;
use Mush\Modifier\Entity\Config\Quantity\ActionCostModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\QuantityModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\SideEffectPercentageModifierConfig;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_config')]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'base' => ModifierConfig::class,
    'quantity' => QuantityModifierConfig::class,
    'action_cost' => ActionCostModifierConfig::class,
    'side_effect_percentage' => SideEffectPercentageModifierConfig::class
])]
abstract class ModifierConfig
{

    public const EVERY_REASONS = 'every_reasons';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    private string $reach;

    private string $name;

    private array $targetEvents = [];

    public function __construct(string $name, string $reach)
    {
        $this->name = $name;
        $this->reach = $reach;
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
        return isset($reasons) && ($reasons === self::EVERY_REASONS || in_array($eventReason, $reasons));
    }

    public abstract function modify(AbstractGameEvent $event);

    public function getTargetEvents(): array
    {
        return $this->targetEvents;
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

}
