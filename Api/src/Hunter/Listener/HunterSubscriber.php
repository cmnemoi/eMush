<?php

namespace Mush\Hunter\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        HunterServiceInterface $hunterService,
        NeronMessageServiceInterface $neronMessageService,
    ) {
        $this->hunterService = $hunterService;
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->hunterService->unpoolHunters($daedalus, $event->getTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::HUNTER_ARRIVAL,
            $daedalus,
            [],
            $event->getTime(),
        );
    }
}
