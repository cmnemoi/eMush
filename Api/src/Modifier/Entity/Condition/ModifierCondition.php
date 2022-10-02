<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_condition')]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'base' => ModifierCondition::class,
    'minimum_player_in_place' => MinimumPlayerInPlaceModifierCondition::class,
    'maximum_player_in_place' => MaximumPlayerInPlaceModifierCondition::class,
    'random' => RandomModifierCondition::class,
    'player_has_status' => PlayerHasStatusModifierCondition::class,
    'equipment_in_place' => EquipmentInPlaceModifierCondition::class,
    'equipment_remain_charges' => EquipmentRemainChargesModifierCondition::class,
    'cycle_even' => CycleEvenModifierCondition::class
])]
abstract class ModifierCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToMany(targetEntity: ModifierConfig::class)]
    private Collection $configs;

    public function __construct()
    {
        $this->configs = new ArrayCollection();
    }

    public abstract function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService) : bool;

    public function getId(): int
    {
        return $this->id;
    }

    protected function getPlace(ModifierHolder $holder) : ?Place {
        if ($holder instanceof Place) {
            return $holder;
        }

        if ($holder instanceof GameEquipment || $holder instanceof Player) {
            return $holder->getPlace();
        }

        return null;
    }

    public function getConfigs(): Collection
    {
        return $this->configs;
    }

}