<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Event\CycleEvent;
use Mush\Game\Event\DayEvent;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Room\Enum\RoomEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;
    private GameConfig $gameConfig;

    /**
     * DaedalusSubscriber constructor.
     */
    public function __construct(
        DaedalusServiceInterface $daedalusService,
        GameEquipmentServiceInterface $gameEquipmentService,
        EventDispatcherInterface $eventDispatcher,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->daedalusService = $daedalusService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if ($event->getGameEquipment() || $event->getPlayer() || $event->getRoom() || $event->getStatus()) {
            return;
        }
        $daedalus = $event->getDaedalus();
        $newDay = false;
        $daedalus->setCycle($daedalus->getCycle() + 1);

        if ($daedalus->getCycle() === ((24 / $this->gameConfig->getCycleLength()) + 1)) {
            $newDay = true;
            $daedalus->setCycle(1);
            $daedalus->setDay($daedalus->getDay() + 1);
        }

        foreach ($daedalus->getPlayers() as $player) {
            $newPlayerCycle = new CycleEvent($daedalus, $event->getTime());
            $newPlayerCycle->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($daedalus->getRooms() as $room) {
            $newRoomCycle = new CycleEvent($daedalus, $event->getTime());
            $newRoomCycle->setRoom($room);
            $this->eventDispatcher->dispatch($newRoomCycle, CycleEvent::NEW_CYCLE);
        }

        if ($newDay) {
            $dayEvent = new DayEvent($daedalus, $event->getTime());
            $this->eventDispatcher->dispatch($dayEvent, DayEvent::NEW_DAY);
        }

        //Handle oxygen loss
        $this->daedalus->addOxygen(-1);
        if ($this->daedalus->getRoomByName(RoomEnum::CENTER_ALPHA_STORAGE)->getEquipment()->
            filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getName() === EquipmentEnum::OXYGEN_TANK)
            ->first()->isBroken()) {
            $this->daedalus->addOxygen(-1);
        }
        if ($this->daedalus->getRoomByName(RoomEnum::CENTER_BRAVO_STORAGE)->getEquipment()->
            filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getName() === EquipmentEnum::OXYGEN_TANK)
            ->first()->isBroken()) {
            $this->daedalus->addOxygen(-1);
        }

        if ($this->daedalus->getOxygen < 0) {
            $this->daedalus->setOxygen = 0;
            //@TODO kill a random player
        }

        //@TODO When everything is added check that everithing happens in the right order
        $this->daedalusService->persist($daedalus);
    }
}
