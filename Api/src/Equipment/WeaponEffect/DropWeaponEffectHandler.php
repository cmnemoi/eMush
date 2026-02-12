<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;

final readonly class DropWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(private GameEquipmentServiceInterface $gameEquipmentService) {}

    public function getName(): string
    {
        return WeaponEffectEnum::DROP_WEAPON->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $effect->getWeapon(),
            newHolder: $effect->getAttacker()->getPlace(),
            visibility: VisibilityEnum::HIDDEN,
            tags: $effect->getTags(),
            time: $effect->getTime(),
        );
    }

    public function isModifyingDamages(): bool
    {
        return false;
    }
}
