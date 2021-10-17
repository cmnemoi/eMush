<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Event\DaedalusInitEvent;
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
            DaedalusInitEvent::NEW_DAEDALUS => 'onDaedalusNew',
        ];
    }

    public function onDaedalusNew(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->channelService->createPublicChannel($daedalus);
    }
}
