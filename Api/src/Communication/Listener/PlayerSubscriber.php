<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
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

        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

        if ($endCause === null) {
            throw new \LogicException('Player should die with a reason');
        }
        $this->neronMessageService->createPlayerDeathMessage($player, $endCause, $time);

        $channels = $this->channelService->getPlayerChannels($player, true);

        foreach ($channels as $channel) {
            $this->channelService->exitChannel($player, $channel, $time, PlayerEvent::DEATH_PLAYER);
        }
    }
}
