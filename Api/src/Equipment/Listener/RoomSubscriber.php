<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEventInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEventInterface;
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
            RoomEventInterface::ELECTRIC_ARC => 'onElectricArc',
        ];
    }

    public function onElectricArc(RoomEventInterface $event): void
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
                $equipmentEvent = new EquipmentEventInterface(
                    $equipment,
                    $event->getPlace(),
                    VisibilityEnum::PUBLIC,
                    RoomEventInterface::ELECTRIC_ARC,
                    $event->getTime()
                );
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEventInterface::EQUIPMENT_BROKEN);
            }
        }
    }
}
