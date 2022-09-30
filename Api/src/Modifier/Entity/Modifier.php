<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierConditionCollection;
use Mush\Modifier\Entity\Quantity\ActionCost\ActionCostModifier;
use Mush\Modifier\Entity\Quantity\QuantityModifier;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Exception\LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'modifier')]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'base' => Modifier::class,
    'delta' => QuantityModifier::class,
    'action_cost' => ActionCostModifier::class
])]
abstract class Modifier
{

    public const EVERY_REASONS = 'every_reasons';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    private string $name;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $equipment = null;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private ?Daedalus $daedalus = null;

    private array $targetEvents = [];

    public function __construct(ModifierHolder $holder, string $name)
    {
        $this->setModifierHolder($holder);
        $this->name = $name;
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
        return isset($reasons) && in_array($eventReason, $reasons);
    }

    public abstract function modify(AbstractGameEvent $event);

    public function getTargetEvents(): array
    {
        return $this->targetEvents;
    }

    private function setModifierHolder(ModifierHolder $holder) : void {
        if ($holder instanceof Player) {
            $this->player = $holder;
        } elseif ($holder instanceof Place) {
            $this->place = $holder;
        } elseif ($holder instanceof Daedalus) {
            $this->daedalus = $holder;
        } elseif ($holder instanceof GameEquipment) {
            $this->equipment = $holder;
        }
    }

    public function getModifierHolder(): ModifierHolder
    {
        if ($this->player) {
            return $this->player;
        } elseif ($this->place) {
            return $this->place;
        } elseif ($this->daedalus) {
            return $this->daedalus;
        } elseif ($this->equipment) {
            return $this->equipment;
        } else {
            throw new LogicException("This modifier don't have any valid holder");
        }
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
