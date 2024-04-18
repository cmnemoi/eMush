<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceInitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceInitSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EquipmentServiceInterface $equipmentService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EquipmentServiceInterface $equipmentService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->equipmentService = $equipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlaceInitEvent::NEW_PLACE => 'onNewPlace',
        ];
    }

    public function onNewPlace(PlaceInitEvent $event): void
    {
        $place = $event->getPlace();
        $placeConfig = $event->getPlaceConfig();
        $daedalus = $place->getDaedalus();
        $reasons = $event->getTags();
        $time = $event->getTime();

        foreach ($placeConfig->getItems() as $itemName) {
            $item = $this->equipmentService->findByNameAndDaedalus($itemName, $daedalus);

            $gameItem = $this->gameEquipmentService->createGameEquipment($item, $place, $reasons, $time);
        }

        foreach ($placeConfig->getEquipments() as $equipmentName) {
            $equipment = $this->equipmentService->findByNameAndDaedalus($equipmentName, $daedalus);

            $gameEquipment = $this->gameEquipmentService->createGameEquipment($equipment, $place, $reasons, $time);
        }

        // initialize doors
        $doorConfig = $this->equipmentService->findByNameAndDaedalus(EquipmentEnum::DOOR, $daedalus);
        // @FIXME how to simplify that?
        foreach ($placeConfig->getDoors() as $doorName) {
            if (
                $roomDoor = $daedalus->getRooms()->filter( // If door already exist
                    static function (Place $room) use ($doorName) {
                        return $room->getDoors()->exists(static function ($key, Door $door) use ($doorName) {
                            return $door->getName() === $doorName;
                        });
                    }
                )->first()
            ) {
                $door = $roomDoor->getDoors()->filter(static function (Door $door) use ($doorName) {
                    return $door->getName() === $doorName;
                })->first();
            } else { // else create new door
                $door = new Door($place);
                $door
                    ->setName($doorName)
                    ->setEquipment($doorConfig);
            }

            $door->addRoom($place);
            $this->gameEquipmentService->persist($door);
        }
    }
}
