<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\CycleEvent;
use Mush\Game\Event\DayEvent;
use Mush\Player\Enum\EndCauseEnum as EnumEndCauseEnum;
use Mush\Room\Enum\RoomEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
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
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event): void
    {
        if ($event->getGameEquipment() || $event->getPlayer() || $event->getRoom() || $event->getStatus()) {
            return;
        }
        $daedalus = $event->getDaedalus();
        $daedalus->setCycle($daedalus->getCycle() + 1);

        if ($this->handleDaedalusEnd($daedalus)) {
            return; //@FIXME: should we continue cycle event if daedalus is destructed?
        }

        $this->dispatchCycleChangeEvent($daedalus, $event->getTime());

        $daedalus = $this->handleOxygen($daedalus);

        //@TODO When everything is added check that everything happens in the right order
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

        if ($daedalus->getCycle() === ((24 / $gameConfig->getCycleLength()) + 1)) {
            $newDay = true;
            $daedalus->setCycle(1);
            $daedalus->setDay($daedalus->getDay() + 1);
        }

        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerCycle = new CycleEvent($daedalus, $time);
            $newPlayerCycle->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($daedalus->getRooms() as $room) {
            if ($room->getName() !== RoomEnum::GREAT_BEYOND) {
                $newRoomCycle = new CycleEvent($daedalus, $time);
                $newRoomCycle->setRoom($room);
                $this->eventDispatcher->dispatch($newRoomCycle, CycleEvent::NEW_CYCLE);
            }
        }

        if ($newDay) {
            $dayEvent = new DayEvent($daedalus, $time);
            $this->eventDispatcher->dispatch($dayEvent, DayEvent::NEW_DAY);
        }
    }
}
