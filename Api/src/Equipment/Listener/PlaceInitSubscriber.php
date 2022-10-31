<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceInitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceInitSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(GameEquipmentServiceInterface $gameEquipmentService)
    {
        $this->gameEquipmentService = $gameEquipmentService;
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
        $reason = $event->getReasons()[0];
        $time = $event->getTime();

        foreach ($placeConfig->getItems() as $itemName) {
            $item = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $item) => $item->getName() === $itemName)->first()
            ;

            $gameItem = $this->gameEquipmentService->createGameEquipment($item, $place, $reason);
        }

        foreach ($placeConfig->getEquipments() as $equipmentName) {
            $equipment = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $equipment) => $equipment->getName() === $equipmentName)->first()
            ;

            $gameEquipment = $this->gameEquipmentService->createGameEquipment($equipment, $place, $reason);
        }

        // initialize doors
        $doorConfig = $daedalus
            ->getGameConfig()
            ->getEquipmentsConfig()
            ->filter(fn (EquipmentConfig $item) => $item->getName() === EquipmentEnum::DOOR)->first()
        ;

        // @FIXME how to simplify that?
        foreach ($placeConfig->getDoors() as $doorName) {
            if (
                $roomDoor = $daedalus->getRooms()->filter( // If door already exist
                    function (Place $room) use ($doorName) {
                        return $room->getDoors()->exists(function ($key, Door $door) use ($doorName) {
                            return $door->getName() === $doorName;
                        });
                    }
                )->first()
            ) {
                $door = $roomDoor->getDoors()->filter(function (Door $door) use ($doorName) {
                    return $door->getName() === $doorName;
                })->first();
            } else { // else create new door
                $door = new Door();
                $door
                    ->setName($doorName)
                    ->setHolder($place)
                    ->setEquipment($doorConfig)
                ;
            }

            $door->addRoom($place);
            $this->gameEquipmentService->persist($door);
        }
    }
}
