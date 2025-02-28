<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\Game\Service\RandomService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * Weapon Effect that breaks random equipment in the room.
 */
final readonly class DestroyOrBreakRandomItemsWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private RandomService $randomService,
        private EventService $eventService,
        private DeleteEquipmentServiceInterface $deleteEquipment,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::DAMAGE_RANDOM_ITEM->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $place = $effect->getAttacker()->getPlace();
        $numberOfEquipmentToBreak = $effect->getQuantity();
        $breakableItems = $place->getDestroyableOrBreakableWorkingEquipments();

        $equipmentToBreak = $this->randomService->getRandomElements($breakableItems->toArray(), $numberOfEquipmentToBreak);

        /** @var GameEquipment $equipment */
        foreach ($equipmentToBreak as $equipment) {
            if ($equipment->isBreakable()) {
                $this->statusService->createStatusFromName(
                    statusName: EquipmentStatusEnum::BROKEN,
                    holder: $equipment,
                    tags: $effect->getTags(),
                    time: $effect->getTime(),
                    visibility: VisibilityEnum::PUBLIC,
                );
            } else {
                $this->deleteEquipment->execute($equipment, VisibilityEnum::PUBLIC, tags: $effect->getTags(), time: new \DateTime());
            }
        }
    }
}
