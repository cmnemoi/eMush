<?php

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
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
        $reasons = $event->getTags();
        $time = $event->getTime();

        $randomItemPlaces = $daedalusConfig->getRandomItemPlaces();

        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $roomName = $randomItemPlaces->getPlaces()[$this->randomService->random(0, \count($randomItemPlaces->getPlaces()) - 1)];
                $room = $daedalus->getRooms()->filter(static fn (Place $room) => $roomName === $room->getName())->first();

                $this->gameEquipmentService->createGameEquipmentFromName(
                    $itemName,
                    $room,
                    $reasons,
                    $time
                );
            }
        }
    }
}
