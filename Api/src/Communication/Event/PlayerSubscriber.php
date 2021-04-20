<?php

namespace Mush\Communication\Event;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;

    public function __construct(
        ChannelServiceInterface $channelService,
        MessageServiceInterface $messageService
    ) {
        $this->channelService = $channelService;
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $this->channelService->invitePlayerToPublicChannel($player);
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $playerName = $player->getCharacterConfig()->getName();
        $cause = $event->getReason();

        switch ($playerName) {
            case CharacterEnum::RALUCA:
                $message = NeronMessageEnum::RALUCA_DEATH;
                break;
            case CharacterEnum::JANICE:
                $message = NeronMessageEnum::JANICE_DEATH;
                break;
            default:
                if ($cause === EndCauseEnum::ASPHYXIA) {
                    $message = NeronMessageEnum::ASPHYXIA_DEATH;
                } else {
                    $message = NeronMessageEnum::PLAYER_DEATH;
                }
                break;
        }

        $parameters = ['player' => $playerName, 'cause' => $cause];
        $this->messageService->createNeronMessage($message, $player->getDaedalus(), $parameters, $event->getTime());
    }
}
