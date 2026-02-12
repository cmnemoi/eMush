<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\AbstractWeaponEffectHandler;

class WeaponEffectHandlerService
{
    private array $strategies = [];

    public function addStrategy(AbstractWeaponEffectHandler $handler): void
    {
        $this->strategies[$handler->getName()] = $handler;
    }

    public function handle(WeaponEffect $event, bool $modifyDamage): DamageSpread
    {
        $handler = $this->getWeaponEffectHandler($event->getEventName());

        if ($modifyDamage === $handler->isModifyingDamages()) {
            $handler->handle($event);
        }

        return $event->getDamageSpread();
    }

    private function getWeaponEffectHandler(string $name): AbstractWeaponEffectHandler
    {
        if (!isset($this->strategies[$name])) {
            throw new \InvalidArgumentException("Unknown weapon effect handler: {$name}");
        }

        return $this->strategies[$name];
    }
}
