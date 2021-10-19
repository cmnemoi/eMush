<?php

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
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
            DaedalusInitEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusConfig = $event->getDaedalusConfig();
        $reason = $event->getReason();
        $time = $event->getTime();

        $randomItemPlaces = $daedalusConfig->getRandomItemPlace();

        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $item = $daedalus
                    ->getGameConfig()
                    ->getEquipmentsConfig()
                    ->filter(fn (EquipmentConfig $item) => $item->getName() === $itemName)
                    ->first()
                ;

                $roomName = $randomItemPlaces
                    ->getPlaces()[$this->randomService->random(0, count($randomItemPlaces->getPlaces()) - 1)]
                ;
                $room = $daedalus->getRooms()->filter(fn (Place $room) => $roomName === $room->getName())->first();

                $item = $this->gameEquipmentService->createGameEquipment($item, $room, $reason, $time);
            }
        }
    }
}
