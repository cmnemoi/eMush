<?php

namespace Mush\Chat\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Enum\ChatActionEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Event\ChannelEvent;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelSubscriber implements EventSubscriberInterface
{
    private const PLAYER_LEAVE_CHANNEL = [
        ChatActionEnum::EXIT => NeronMessageEnum::PLAYER_LEAVE_CHAT,
        PlayerEvent::DEATH_PLAYER => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ActionEnum::GO_BERSERK->value => NeronMessageEnum::PLAYER_LEAVE_CHAT_MUTATED,
        ActionEnum::DROP->value => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ActionEnum::MOVE->value => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        EquipmentStatusEnum::BROKEN => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        PlayerStatusEnum::LOST => NeronMessageEnum::PLAYER_LEAVE_CHAT_LOST,
    ];

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
            ChannelEvent::NEW_CHANNEL => 'onNewChannel',
            ChannelEvent::JOIN_CHANNEL => 'onJoinChannel',
            ChannelEvent::EXIT_CHANNEL => 'onExitChannel',
        ];
    }

    public function onNewChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getAuthor()) {
            $this->channelService->invitePlayer($player, $channel);
        }
    }

    public function onJoinChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getAuthor()) {
            $this->channelService->addPlayer($player->getPlayerInfo(), $channel);

            $this->messageService->createSystemMessage(
                NeronMessageEnum::PLAYER_ENTER_CHAT,
                $channel,
                ['character' => $player->getName()],
                new \DateTime()
            );
        }
    }

    public function onExitChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getAuthor()) {
            $this->channelService->removePlayer($player->getPlayerInfo(), $channel);

            $key = $event->mapLog(self::PLAYER_LEAVE_CHANNEL);

            if ($key !== null) {
                $this->messageService->createSystemMessage(
                    $key,
                    $channel,
                    ['character' => $player->getName()],
                    new \DateTime()
                );
            }
        }
    }
}
