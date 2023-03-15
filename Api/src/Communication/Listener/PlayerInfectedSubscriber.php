<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerInfectedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerInfectedSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;

    public function __construct(ChannelServiceInterface $channelService, MessageServiceInterface $messageService)
    {
        $this->channelService = $channelService;
        $this->messageService = $messageService;
    }


    public static function getSubscribedEvents() : array
    {
        return [
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onInfectionPlayer(PlayerInfectedEvent $event): void
    {
        $daedalusInfo = $event->getPlayer()->getDaedalus()->getDaedalusInfo();
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();
        $params = $event->getLogParameters();
        $this->messageService->createSystemMessage('mush_infect_event', $mushChannel, $params, $time);
    }

    public function onConversionPlayer(PlayerInfectedEvent $event): void
    {
        $daedalusInfo = $event->getPlayer()->getDaedalus()->getDaedalusInfo();
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();
        $params = $event->getLogParameters();
        $this->messageService->createSystemMessage('mush_convert_event', $mushChannel, $params, $time);
    }
}
