<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private NeronMessageServiceInterface $neronMessageService;
    private MessageServiceInterface $messageService;

    public function __construct(
        ChannelServiceInterface $channelService,
        NeronMessageServiceInterface $neronMessageService,
        MessageServiceInterface $messageService
    ) {
        $this->channelService = $channelService;
        $this->neronMessageService = $neronMessageService;
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::GAIN_TITLE => 'onPlayerGainTitle',
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

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $params = $event->getLogParameters();
        $params['quantity'] = $event->getPlayer()->getSpores();
        $key = $event->mapLog(MushMessageEnum::PLAYER_INFECTION_LOGS);
        if ($key === null) {
            return;
        }

        $daedalusInfo = $event->getPlayer()->getDaedalus()->getDaedalusInfo();
        /** @var Channel $mushChannel */
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();

        $this->messageService->createSystemMessage($key, $mushChannel, $params, $time);
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $daedalusInfo = $event->getPlayer()->getDaedalus()->getDaedalusInfo();
        /** @var Channel $mushChannel */
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();
        $params = $event->getLogParameters();

        $this->messageService->createSystemMessage(MushMessageEnum::MUSH_CONVERT_EVENT, $mushChannel, $params, $time);

        $this->channelService->addPlayerToMushChannel($event->getPlayer());
    }

    public function onPlayerGainTitle(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $time = $event->getTime();

        $title = $event->mapLog(TitleEnum::TITLES_MAP);

        if ($title === null) {
            throw new \LogicException('Player needs a specific title to gain');
        }
        $this->neronMessageService->createTitleAttributionMessage($player, $title, $time);
    }
}
