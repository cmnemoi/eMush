<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Enum\EndCauseEnum as EnumEndCauseEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\Room\Event\RoomCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        GameEquipmentServiceInterface $gameEquipmentService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->daedalusService = $daedalusService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalus->setCycle($daedalus->getCycle() + 1);
        $daedalus->setCycleStartedAt($event->getTime());

        if ($this->handleDaedalusEnd($daedalus)) {
            return; //@FIXME: should we continue cycle event if daedalus is destructed?
        }

        $this->dispatchCycleChangeEvent($daedalus, $event->getTime());

        $daedalus = $this->handleOxygen($daedalus);

        //@TODO When everything is added check that everything happens in the right order
        $this->daedalusService->persist($daedalus);
    }

    public function onNewDay(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerDay = new PlayerCycleEvent($player, $event->getTime());

            $this->eventDispatcher->dispatch($newPlayerDay, PlayerCycleEvent::PLAYER_NEW_DAY);
        }

        /** @var Room $room */
        foreach ($daedalus->getRooms() as $room) {
            if ($room->getName() !== RoomEnum::GREAT_BEYOND) {
                $newRoomDay = new RoomCycleEvent($room, $event->getTime());
                $this->eventDispatcher->dispatch($newRoomDay, RoomCycleEvent::ROOM_NEW_DAY);
            }
        }

        //reset spore count
        $daedalus->setSpores($daedalus->getDailySpores());

        $this->daedalusService->persist($daedalus);
    }

    private function handleDaedalusEnd(Daedalus $daedalus): bool
    {
        if ($daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->isEmpty() &&
            !$daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->isEmpty()
        ) {
            $endDaedalusEvent = new DaedalusEvent($daedalus);
            $endDaedalusEvent->setReason(EnumEndCauseEnum::KILLED_BY_NERON);
            $this->eventDispatcher->dispatch($endDaedalusEvent, DaedalusEvent::END_DAEDALUS);

            return true;
        }

        return false;
    }

    private function handleOxygen(Daedalus $daedalus): Daedalus
    {
        //Handle oxygen loss
        $oxygenLoss = 1;

        //@TODO: We shouldn't assume the oxygen tank are in these storages
        if (($alphaStorageRoom = $daedalus->getRoomByName(RoomEnum::CENTER_ALPHA_STORAGE)) &&
            $alphaStorageRoom
                ->getEquipments()
                ->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getName() === EquipmentEnum::OXYGEN_TANK)
                ->first()
                ->isBroken()
        ) {
            $oxygenLoss = $oxygenLoss + 1;
        }
        if (($bravoStorageRoom = $daedalus->getRoomByName(RoomEnum::CENTER_BRAVO_STORAGE)) &&
            $bravoStorageRoom
                ->getEquipments()
                ->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getName() === EquipmentEnum::OXYGEN_TANK)
                ->first()
                ->isBroken()
        ) {
            $oxygenLoss = $oxygenLoss + 1;
        }

        if ($daedalus->getOxygen() <= $oxygenLoss) {
            $this->daedalusService->getRandomAsphyxia($daedalus);
        }
        $this->daedalusService->changeOxygenLevel($daedalus, -$oxygenLoss);

        return $daedalus;
    }

    private function dispatchCycleChangeEvent(Daedalus $daedalus, \DateTime $time): void
    {
        $newDay = false;

        $gameConfig = $daedalus->getGameConfig();

        if ($daedalus->getCycle() === $gameConfig->getCyclePerGameDay() + 1) {
            $newDay = true;
            $daedalus->setCycle(1);
            $daedalus->setDay($daedalus->getDay() + 1);
        }

        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerCycle = new PlayerCycleEvent($player, $time);
            $this->eventDispatcher->dispatch($newPlayerCycle, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }

        foreach ($daedalus->getRooms() as $room) {
            if ($room->getName() !== RoomEnum::GREAT_BEYOND) {
                $newRoomCycle = new RoomCycleEvent($room, $time);
                $this->eventDispatcher->dispatch($newRoomCycle, RoomCycleEvent::ROOM_NEW_CYCLE);
            }
        }

        foreach ($this->gameEquipmentService->getDoorsByDaedalus($daedalus) as $door) {
            $itemNewCycle = new EquipmentCycleEvent($door, $daedalus, $time);
            $this->eventDispatcher->dispatch($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }

        if ($newDay) {
            $dayEvent = new DaedalusCycleEvent($daedalus, $time);
            $this->eventDispatcher->dispatch($dayEvent, DaedalusCycleEvent::DAEDALUS_NEW_DAY);
        }
    }
}
