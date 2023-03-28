<?php

namespace Mush\Hunter\Listener;

use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;

    public function __construct(HunterServiceInterface $hunterService)
    {
        $this->hunterService = $hunterService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
            HunterPoolEvent::POOL_HUNTERS => 'onPoolHunters',
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $nbHuntersToUnpool = $event->getNbHunters();

        $this->hunterService->unpoolHunters($daedalus, $nbHuntersToUnpool);
    }

    public function onPoolHunters(HunterPoolEvent $event): void
    {
    }
}
