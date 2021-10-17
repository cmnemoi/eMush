<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Config\Door;
use Mush\Equipment\Entity\Config\GameItem;
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
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        //@FIXME does electric arc break everything?
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->isBreakable()) {
                $equipmentEvent = new EquipmentEvent(
                    $equipment,
                    $event->getPlace(),
                    VisibilityEnum::PUBLIC,
                    RoomEvent::ELECTRIC_ARC,
                    $event->getTime()
                );
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }
    }
}
