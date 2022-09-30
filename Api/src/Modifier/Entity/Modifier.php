<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
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
    'modifier' => Modifier::class,
    'delta_modifier' => QuantityModifier::class,
    'action_cost_modifier' => ActionCostModifier::class
])]
abstract class Modifier
{
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

    public function __construct(ModifierHolder $holder, string $name)
    {
        $this->setModifierHolder($holder);
        $this->name = $name;
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

}
