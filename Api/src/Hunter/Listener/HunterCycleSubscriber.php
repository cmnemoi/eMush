<?php

namespace Mush\Hunter\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private HunterServiceInterface $hunterService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        HunterServiceInterface $hunterService,
        RandomServiceInterface $randomService
    ) {
        $this->eventService = $eventService;
        $this->hunterService = $hunterService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $attackingHunters = $daedalus->getAttackingHunters();
        $this->hunterService->makeHuntersShoot($attackingHunters);

        $this->hunterService->updateDaedalusHunterPoints($daedalus);

        $hunterSpawnRate = $daedalus->getGameConfig()->getDifficultyConfig()->getHunterSpawnRate();
        if ($this->randomService->isSuccessful($hunterSpawnRate)) {
            $unpoolHunterEvent = new HunterPoolEvent(
                $daedalus,
                tags: $event->getTags(),
                time: $event->getTime()
            );

            $this->eventService->callEvent($unpoolHunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        }
    }
}
