<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\WeaponEffect\BreakRandomItemsWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\BreakWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\DestroyWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\DropWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\InflictInjuryWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\InflictRandomInjuryWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyDamageWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyMaxDamageWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\MultiplyDamageOnMushTargetWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\OneShotWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\RemoveActionPointsWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\SplashDamageAllWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\SplashInjuryWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;

/**
 * Class storing the various information needed to create events.
 */
#[ORM\Entity]
#[ORM\Table(name: 'event_config')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'variable_event_config' => VariableEventConfig::class,
    'planet_sector_event_config' => PlanetSectorEventConfig::class,
    'weapon_event_config' => WeaponEventConfig::class,
    'one_shot_weapon_effect_config' => OneShotWeaponEffectConfig::class,
    'inflict_random_injury_weapon_effect_config' => InflictRandomInjuryWeaponEffectConfig::class,
    'inflict_injury_weapon_effect_config' => InflictInjuryWeaponEffectConfig::class,
    'remove_action_points_weapon_effect_config' => RemoveActionPointsWeaponEffectConfig::class,
    'modify_max_damage_weapon_effect_config' => ModifyMaxDamageWeaponEffectConfig::class,
    'modify_damage_weapon_effect_config' => ModifyDamageWeaponEffectConfig::class,
    'break_weapon_effect_config' => BreakWeaponEffectConfig::class,
    'drop_weapon_effect_config' => DropWeaponEffectConfig::class,
    'multiply_damage_on_mush_target_weapon_effect_config' => MultiplyDamageOnMushTargetWeaponEffectConfig::class,
    'destroy_weapon_effect_config' => DestroyWeaponEffectConfig::class,
    'splash_damage_all_weapon_effect_config' => SplashDamageAllWeaponEffectConfig::class,
    'splash_injury_weapon_effect_config' => SplashInjuryWeaponEffectConfig::class,
    'break_random_items_weapon_effect_config' => BreakRandomItemsWeaponEffectConfig::class,
])]
abstract class AbstractEventConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $eventName;

    public function __construct(string $name = '', string $eventName = '')
    {
        $this->name = $name;
        $this->eventName = $eventName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function revertEvent(): ?self
    {
        return null;
    }

    public function getTranslationKey(): ?string
    {
        return $this->eventName;
    }

    public function getTranslationParameters(): array
    {
        return [];
    }
}
