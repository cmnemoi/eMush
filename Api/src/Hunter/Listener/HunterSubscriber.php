<?php

namespace Mush\Hunter\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;

    public function __construct(
        HunterServiceInterface $hunterService,
    ) {
        $this->hunterService = $hunterService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => ['onUnpoolHunters', EventPriorityEnum::HIGH], // spawn hunters before any related events
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->hunterService->unpoolHunters($daedalus, $event->getTags(), $event->getTime());
    }
}
