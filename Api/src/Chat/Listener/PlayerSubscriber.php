<?php

namespace Mush\Chat\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
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
            PlayerEvent::TITLE_ATTRIBUTED => 'onPlayerTitleAttributed',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $time = $event->getTime();
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);
        $neron = $player->getDaedalus()->getNeron();

        $channels = $this->channelService->getPlayerChannels($player, true);
        foreach ($channels as $channel) {
            $this->channelService->exitChannel($player, $channel, $time, PlayerEvent::DEATH_PLAYER);
        }

        if ($player->getPlace()->hasStatus(PlaceStatusEnum::DELOGGED->toString())) {
            return;
        }

        if ($endCause === null) {
            throw new \LogicException('Player should die with a reason');
        }
        if (EndCauseEnum::isDeathEndCause($endCause) && $neron->areDeathAnnouncementsActive()) {
            $this->neronMessageService->createPlayerDeathMessage($player, $endCause, $time);
        }
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        // If the player is Mush, we want to log only mush traps in Mush channel
        if (!$event->hasTag(PlanetSectorEvent::MUSH_TRAP) && $player->isMush()) {
            return;
        }

        $params = $event->getLogParameters();
        $params['quantity'] = $player->getSpores();
        $params['is_player_mush'] = $player->isMush() ? 'true' : 'false';
        $key = $event->mapLog(MushMessageEnum::PLAYER_INFECTION_LOGS);
        if ($key === null) {
            return;
        }
        if ($key === MushMessageEnum::INFECT_CAT) {
            $catHolder = $player->hasEquipmentByName(ItemEnum::SCHRODINGER) ? $player : $player->getPlace();
            $mush = $catHolder->getEquipmentByNameOrThrow(ItemEnum::SCHRODINGER)->getStatusByNameOrThrow(EquipmentStatusEnum::CAT_INFECTED)->getPlayerTargetOrThrow();

            $params['item'] = ItemEnum::SCHRODINGER;
            $params[$mush->getLogKey()] = $mush->getLogName();
        }

        $daedalusInfo = $player->getDaedalusInfo();

        /** @var Channel $mushChannel */
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();

        $this->messageService->createSystemMessage($key, $mushChannel, $params, $time);
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $this->channelService->addPlayerToMushChannel($event->getPlayer());

        // if player exchanged body, we want to add them to the channel without any message
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->value)) {
            return;
        }

        $daedalusInfo = $event->getPlayer()->getDaedalus()->getDaedalusInfo();

        /** @var Channel $mushChannel */
        $mushChannel = $this->channelService->getMushChannel($daedalusInfo);
        $time = $event->getTime();
        $params = $event->getLogParameters();

        $this->messageService->createSystemMessage(MushMessageEnum::MUSH_CONVERT_EVENT, $mushChannel, $params, $time);
    }

    public function onPlayerTitleAttributed(PlayerEvent $event): void
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
