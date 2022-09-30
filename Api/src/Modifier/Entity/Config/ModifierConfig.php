<?php

namespace Mush\Modifier\Entity;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierConditionCollection;
use Mush\Modifier\Entity\Config\Quantity\ActionCostModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\QuantityModifierConfig;
use Mush\Modifier\Entity\Config\Quantity\SideEffectPercentageModifierConfig;
use Doctrine\ORM\Mapping as ORM;

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
    public const EXCLUDE = 'exclude';

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

    public abstract function modify(AbstractModifierHolderEvent $event, EventServiceInterface $eventService);

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
