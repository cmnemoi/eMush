<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Service\DamageEquipmentServiceInterface;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\Game\Service\RandomService;

/**
 * Weapon Effect that breaks random equipment in the room.
 */
final readonly class DestroyOrBreakRandomItemsWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private RandomService $randomService,
        private EventService $eventService,
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private DamageEquipmentServiceInterface $damageEquipmentService,
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
            $this->damageEquipmentService->execute(
                gameEquipment: $equipment,
                tags: $effect->getTags(),
                time: $effect->getTime(),
                visibility: VisibilityEnum::PUBLIC,
            );
        }
    }

    public function isModifyingDamages(): bool
    {
        return false;
    }
}
