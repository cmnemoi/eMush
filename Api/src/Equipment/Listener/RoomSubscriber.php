<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
        ];
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        //@FIXME does electric arc break everythings?
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->isBreakable()) {
                $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::PUBLIC, $event->getTime());
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }
    }
}
