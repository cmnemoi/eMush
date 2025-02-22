<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;

/**
 * Weapon Effect that applies "SplashAll" random damage to everyone in the room except the target. Target can be null (e.g. grenade), meaning no one is spared.
 * Damage is halved for the attacker.
 * Not to be confused with "Splash" damage distribution, which distributes a set amount of damage to random players in the room.
 */
final readonly class SplashDamageAllWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::SPLASH_DAMAGE_ALL->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->inflictDamageToPlayersInRoom($effect);

        $this->inflictBackfireDamageToAttacker($effect);
    }

    private function inflictDamageToPlayersInRoom(WeaponEffect $effect): void
    {
        $place = $effect->getAttacker()->getPlace();

        $targets = $place->getAlivePlayersExcept($effect->getAttacker());

        $damageSpread = $effect->getDamageSpread();

        foreach ($targets as $target) {
            $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);
            $this->removeHealthFromPlayer->execute(
                author: $effect->getAttacker(),
                quantity: $damage,
                player: $target,
                tags: $effect->getTags(),
                visibility: VisibilityEnum::PRIVATE,
            );
        }
    }

    private function inflictBackfireDamageToAttacker(WeaponEffect $effect): void
    {
        $damageSpread = $effect->getDamageSpread();

        $damage = $this->getRandomInteger->execute($damageSpread->min, (int) ceil($damageSpread->max / 2));
        $this->removeHealthFromPlayer->execute(
            author: $effect->getAttacker(),
            quantity: $damage,
            player: $effect->getAttacker(),
            tags: $effect->getTags(),
            visibility: VisibilityEnum::PRIVATE,
        );
    }
}
