<?php

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\Date;

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
        $this->randomService = $randomService,
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
        $reason = $event->getReason();
        $time = $event->getTime();

        $randomItemPlaces = $daedalusConfig->getRandomItemPlace();

        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $roomName = $randomItemPlaces
                    ->getPlaces()[$this->randomService->random(0, count($randomItemPlaces->getPlaces()) - 1)]
                ;
                $room = $daedalus->getRooms()->filter(fn (Place $room) => $roomName === $room->getName())->first();

                $equipment = $this->gameEquipmentService->createGameEquipmentFromName($itemName, $room, $reason, $time);

                $event = new EquipmentEvent(
                    $equipment,
                    true,
                    VisibilityEnum::HIDDEN,
                    DaedalusInitEvent::NEW_DAEDALUS,
                    new \DateTime()
                );
                $this->eventDispatcher->dispatch($event, EquipmentEvent::EQUIPMENT_CREATED);
            }
        }
    }
}
