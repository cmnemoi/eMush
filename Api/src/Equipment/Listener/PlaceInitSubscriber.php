<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentEventReason;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
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
                /** @var Door $door */
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

        if ($this->shouldCreateHalloweenJumpkin($event)) {
            $this->createHalloweenJumpkin($event);
        }
        if ($this->shouldCreateAprilFoolsPavlov($event)) {
            $this->createAprilFoolsPavlov($event);
        }
    }

    private function shouldCreateHalloweenJumpkin(PlaceInitEvent $event): bool
    {
        $place = $event->getPlace();
        $daedalus = $place->getDaedalus();

        return $daedalus->getDaedalusConfig()->getHoliday() === HolidayEnum::HALLOWEEN && $place->getName() === RoomEnum::HYDROPONIC_GARDEN;
    }

    private function createHalloweenJumpkin(PlaceInitEvent $event): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::JUMPKIN,
            equipmentHolder: $event->getPlace(),
            reasons: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::HIDDEN,
        );
    }

    private function shouldCreateAprilFoolsPavlov(PlaceInitEvent $event): bool
    {
        $place = $event->getPlace();
        $daedalus = $place->getDaedalus();

        return $daedalus->getDaedalusConfig()->getHoliday() === HolidayEnum::APRIL_FOOLS && $place->getName() === RoomEnum::LABORATORY;
    }

    private function createAprilFoolsPavlov(PlaceInitEvent $event): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::PAVLOV,
            equipmentHolder: $event->getPlace(),
            reasons: [EquipmentEventReason::AWAKEN_PAVLOV],
            time: $event->getTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
    }
}
