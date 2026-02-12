<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

final readonly class DestroyWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private EventServiceInterface $eventService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::DESTROY_WEAPON->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $effect->getWeapon(),
            author: $effect->getAuthor(),
            visibility: VisibilityEnum::HIDDEN,
            tags: $effect->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    public function isModifyingDamages(): bool
    {
        return false;
    }
}
