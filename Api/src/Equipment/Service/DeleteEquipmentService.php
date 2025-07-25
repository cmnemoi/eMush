<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

final class DeleteEquipmentService implements DeleteEquipmentServiceInterface
{
    public function __construct(private EventServiceInterface $eventService) {}

    public function execute(
        GameEquipment $gameEquipment,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        $tags[] = $gameEquipment->getName();
        $event = new EquipmentEvent(
            equipment: $gameEquipment,
            created: false,
            visibility: $visibility,
            tags: $tags,
            time: $time,
        );

        $holder = $gameEquipment->getHolder();
        if ($holder instanceof Player) {
            $event->setAuthor($holder);
        }

        $this->eventService->callEvent(
            event: $event,
            name: EquipmentEvent::EQUIPMENT_DESTROYED,
        );
    }
}
