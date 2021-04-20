<?php

namespace Mush\Communication\Event;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;

    public function __construct(
        ChannelServiceInterface $channelService,
        MessageServiceInterface $messageService,
    ) {
        $this->channelService = $channelService;
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->channelService->createPublicChannel($daedalus);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->messageService->createNeronMessage(NeronMessageEnum::START_GAME, $daedalus, [], new \DateTime());
    }
}
