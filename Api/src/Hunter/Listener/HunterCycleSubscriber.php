<?php

namespace Mush\Hunter\Listener;

use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterCycleSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;

    public function __construct(
        HunterServiceInterface $hunterService
    ) {
        $this->hunterService = $hunterService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(HunterCycleEvent $event): void
    {
        $attackingHunters = $event->getDaedalus()->getAttackingHunters();
        $this->hunterService->makeHuntersShoot($attackingHunters);
    }
}
