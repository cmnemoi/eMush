<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Service\RandomService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * Weapon Effect that breaks random equipment in the room.
 */
final readonly class BreakRandomItemsWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private RandomService $randomService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::DAMAGE_RANDOM_ITEM->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $place = $effect->getAttacker()->getPlace();
        $numberOfEquipmentToBreak = $effect->getQuantity();
        $breakableItems = $place->getBreakableWorkingEquipments();

        $equipmentToBreak = $this->randomService->getRandomElements($breakableItems->toArray(), $numberOfEquipmentToBreak);

        /** @var GameEquipment $equipment */
        foreach ($equipmentToBreak as $equipment) {
            $this->statusService->createStatusFromName(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $equipment,
                tags: $effect->getTags(),
                time: $effect->getTime(),
            );
        }
    }
}
