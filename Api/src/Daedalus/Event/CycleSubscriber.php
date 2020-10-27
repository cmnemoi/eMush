<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Event\CycleEvent;
use Mush\Game\Event\DayEvent;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;
    private GameConfig $gameConfig;

    /**
     * DaedalusSubscriber constructor.
     * @param DaedalusServiceInterface $daedalusService
     * @param EventDispatcherInterface $eventDispatcher
     * @param GameConfigServiceInterface $gameConfigService
     */
    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->daedalusService = $daedalusService;
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
        if ($event->getGameItem() || $event->getPlayer() || $event->getRoom()) {
            return;
        }
        $daedalus = $event->getDaedalus();

        $daedalus->setCycle($daedalus->getCycle() + 1);

        //If first cycle, new day
        if (($daedalus->getCycle() % (24 / $this->gameConfig->getCycleLength())) === 0) {
            $dayEvent = new DayEvent($daedalus, $event->getTime());
            $this->eventDispatcher->dispatch($dayEvent, DayEvent::NEW_DAY);
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

        $this->daedalusService->persist($daedalus);
    }
}
