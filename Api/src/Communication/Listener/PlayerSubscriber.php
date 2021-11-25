<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Player\Event\PlayerEvent;
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
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $time = $event->getTime();

        if (!($reason = $event->getReason())) {
            throw new \LogicException('Player should die with a reason');
        }
        $this->neronMessageService->createPlayerDeathMessage($player, $reason, $time);

        $channels = $this->channelService->getPlayerChannels($player);
        foreach ($channels as $channel) {
            $this->channelService->exitChannel($player, $channel, $time, PlayerEvent::DEATH_PLAYER);
        }
    }
}
