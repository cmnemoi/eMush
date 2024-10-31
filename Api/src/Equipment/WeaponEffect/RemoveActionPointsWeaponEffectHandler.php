<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Service\RemoveActionPointsFromPlayerServiceInterface;

final readonly class RemoveActionPointsWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(private RemoveActionPointsFromPlayerServiceInterface $removeActionPointsFromPlayer) {}

    public function getName(): string
    {
        return WeaponEffectEnum::REMOVE_ACTION_POINTS->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->removeActionPointsFromPlayer->execute(
            quantity: $effect->getQuantity(),
            player: $effect->applyToShooter() ? $effect->getAttacker() : $effect->getTarget(),
            tags: $effect->getTags(),
            visibility: VisibilityEnum::PRIVATE,
        );
    }
}
