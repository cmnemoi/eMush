<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\DayEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(DaedalusServiceInterface $daedalusService, EventDispatcherInterface $eventDispatcher)
    {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event): void
    {
        if ($event->getGameEquipment() || $event->getPlayer() || $event->getRoom() || $event->getStatus()) {
            return;
        }
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerDay = new DayEvent($daedalus, $event->getTime());
            $newPlayerDay->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerDay, DayEvent::NEW_DAY);
        }

        /** @var Room $room */
        foreach ($daedalus->getRooms() as $room) {
            if ($room->getName() !== RoomEnum::GREAT_BEYOND) {
                $newRoomDay = new DayEvent($daedalus, $event->getTime());
                $newRoomDay->setRoom($room);
                $this->eventDispatcher->dispatch($newRoomDay, DayEvent::NEW_DAY);
            }
        }

        //reset spore count
        $daedalus->setSpores($daedalus->getDailySpores());

        $this->daedalusService->persist($daedalus);
    }
}
