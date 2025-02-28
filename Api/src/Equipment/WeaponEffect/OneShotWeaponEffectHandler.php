<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Player\Service\PlayerServiceInterface;

final readonly class OneShotWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(private PlayerServiceInterface $playerService) {}

    public function getName(): string
    {
        return WeaponEffectEnum::ONE_SHOT->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->playerService->killPlayer(
            player: $effect->applyToShooter() ? $effect->getAttacker() : $effect->getTarget(),
            endReason: $effect->getEndCause(),
            author: $effect->getAttacker(),
        );
    }
}
