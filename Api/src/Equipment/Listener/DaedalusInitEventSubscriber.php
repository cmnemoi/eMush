<?php

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => ['onNewDaedalus', -100], // this can only be done once room have been created
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusConfig = $event->getDaedalusConfig();
        $reason = $event->getReasons()[0];
        $time = $event->getTime();

        $randomItemPlaces = $daedalusConfig->getRandomItemPlace();

        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $roomName = $randomItemPlaces->getPlaces()[$this->randomService->random(0, count($randomItemPlaces->getPlaces()) - 1)];
                $room = $daedalus->getRooms()->filter(fn (Place $room) => $roomName === $room->getName())->first();

                $this->gameEquipmentService->createGameEquipmentFromName(
                    $itemName,
                    $room,
                    $reason
                );
            }
        }
    }
}
