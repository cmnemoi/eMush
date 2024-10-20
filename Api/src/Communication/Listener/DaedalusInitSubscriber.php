<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;

    public function __construct(
        ChannelServiceInterface $channelService,
    ) {
        $this->channelService = $channelService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => ['onDaedalusNew', EventPriorityEnum::HIGH],
        ];
    }

    public function onDaedalusNew(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->channelService->createPublicChannel($daedalus->getDaedalusInfo());
        $this->channelService->createMushChannel($daedalus->getDaedalusInfo());
    }
}
