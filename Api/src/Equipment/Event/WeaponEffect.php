<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Config\WeaponEffect\BackfireWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\InflictInjuryWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyDamageWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyMaxDamageWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\OneShotWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\QuantityWeaponEffectConfig;
use Mush\Equipment\Entity\Config\WeaponEffect\RandomWeaponEffectConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

final class WeaponEffect extends AbstractGameEvent
{
    public function __construct(
        private readonly AbstractEventConfig $weaponEffectConfig,
        private readonly Player $attacker,
        private readonly Player $target,
        private readonly GameItem $weapon,
        private DamageSpread $damageSpread,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
        $this->setEventName($weaponEffectConfig->getEventName());
    }

    public function getAttacker(): Player
    {
        return $this->attacker;
    }

    public function getTarget(): Player
    {
        return $this->target;
    }

    public function getWeapon(): GameItem
    {
        return $this->weapon;
    }

    public function getName(): string
    {
        return $this->weaponEffectConfig->getName();
    }

    public function getDamageSpread(): DamageSpread
    {
        return $this->damageSpread;
    }

    public function getEndCause(): string
    {
        /** @var OneShotWeaponEffectConfig $oneShotEffectConfig */
        $oneShotEffectConfig = $this->weaponEffectConfig;

        return $oneShotEffectConfig->getEndCause();
    }

    public function applyToShooter(): bool
    {
        if (!$this->weaponEffectConfig instanceof BackfireWeaponEffectConfig) {
            throw new \RuntimeException("Only backfire weapon effects can be applied to shooter, got {$this->weaponEffectConfig->getName()}");
        }

        return $this->weaponEffectConfig->applyToShooter();
    }

    public function getInjuryName(): string
    {
        /** @var InflictInjuryWeaponEffectConfig $inflictInjuryWeaponEffectConfig */
        $inflictInjuryWeaponEffectConfig = $this->weaponEffectConfig;

        return $inflictInjuryWeaponEffectConfig->getInjuryName();
    }

    public function getTriggerRate(): int
    {
        if (!$this->weaponEffectConfig instanceof RandomWeaponEffectConfig) {
            throw new \RuntimeException("Only random weapon effects can have a trigger rate, got {$this->weaponEffectConfig->getName()}");
        }

        return $this->weaponEffectConfig->getTriggerRate();
    }

    public function modifyMaxDamage(): void
    {
        $this->damageSpread = new DamageSpread($this->damageSpread->min, $this->damageSpread->max + $this->getMaxDamageModification());
    }

    public function modifyDamage(): void
    {
        $this->damageSpread = new DamageSpread($this->damageSpread->min + $this->getDamageModification(), $this->damageSpread->max + $this->getDamageModification());
    }

    public function getQuantity(): int
    {
        if (!$this->weaponEffectConfig instanceof QuantityWeaponEffectConfig) {
            throw new \RuntimeException("Only quantity weapon effects can have a quantity, got {$this->weaponEffectConfig->getName()}");
        }

        return $this->weaponEffectConfig->getQuantity();
    }

    private function getMaxDamageModification(): int
    {
        if (!$this->weaponEffectConfig instanceof ModifyMaxDamageWeaponEffectConfig) {
            throw new \RuntimeException("Only modify max damage weapon effects can modify max damage, got {$this->weaponEffectConfig->getName()}");
        }

        return $this->weaponEffectConfig->getQuantity();
    }

    private function getDamageModification(): int
    {
        if (!$this->weaponEffectConfig instanceof ModifyDamageWeaponEffectConfig) {
            throw new \RuntimeException("Only modify damage weapon effects can modify damage, got {$this->weaponEffectConfig->getName()}");
        }

        return $this->weaponEffectConfig->getQuantity();
    }
}
