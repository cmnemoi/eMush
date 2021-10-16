<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Player\Event\PlayerEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        ChannelServiceInterface $channelService,
        NeronMessageServiceInterface $neronMessageService
    ) {
        $this->channelService = $channelService;
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEventInterface::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEventInterface $event): void
    {
        if (!($reason = $event->getReason())) {
            throw new \LogicException('Player should die with a reason');
        }
        $this->neronMessageService->createPlayerDeathMessage($event->getPlayer(), $reason, $event->getTime());
    }
}
