<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceInitEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceInitSubscriber implements EventSubscriberInterface
{
    private EquipmentFactoryInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EquipmentFactoryInterface $gameEquipmentService,
                                EventDispatcherInterface  $eventDispatcher)
    {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
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
        $reason = $event->getReason();

        foreach ($placeConfig->getItems() as $itemName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                $itemName,
                $place,
                $reason,
                VisibilityEnum::HIDDEN
            );
        }

        foreach ($placeConfig->getEquipments() as $equipmentName) {
            $this->gameEquipmentService->createGameEquipment(
                $equipmentName,
                $place,
                $reason,
                VisibilityEnum::HIDDEN
            );
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
                $door = $this->gameEquipmentService->createDoor($doorName, $place, $doorConfig);
            }

            $door->addRoom($place);
        }
    }
}
