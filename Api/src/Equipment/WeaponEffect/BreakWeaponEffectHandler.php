<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final readonly class BreakWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private StatusServiceInterface $statusService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::BREAK_WEAPON->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $effect->getWeapon(),
            tags: $effect->getTags(),
            time: $effect->getTime(),
        );
    }
}
