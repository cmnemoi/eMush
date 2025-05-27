<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        StatusServiceInterface $statusService
    ) {
        $this->eventService = $eventService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        /** @var GameEquipment $equipment */
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken()
                && !($equipment instanceof Door)
                && !($equipment instanceof GameItem)
                && $equipment->isBreakable()
            ) {
                $this->statusService->createStatusFromName(
                    EquipmentStatusEnum::BROKEN,
                    $equipment,
                    $event->getTags(),
                    $event->getTime(),
                    null,
                    VisibilityEnum::PUBLIC
                );
            }
        }
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        foreach ($event->getPlace()->getEquipments() as $equipment) {
            $equipmentEvent = new EquipmentEvent(
                $equipment,
                false,
                VisibilityEnum::HIDDEN,
                $event->getTags(),
                $event->getTime()
            );

            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DELETE);
        }
    }
}
