<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::END_PLAYER => ['onPlayerEnd', EventPriorityEnum::LOWEST],
            PlayerEvent::PLAYER_GOT_LIKED => ['onPlayerGotLiked', EventPriorityEnum::LOWEST],
        ];
    }

    public function onPlayerEnd(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $nbSleptCycles = $player->getPlayerInfo()->getStatistics()->getSleptCycles();

        if ($closedPlayer->getDayDeath() < 7 || $nbSleptCycles > 0) {
            return;
        }

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $player->getUser()->getId(),
                statisticName: StatisticEnum::GAME_WITHOUT_SLEEP,
                language: $player->getLanguage(),
            )
        );
    }

    public function onPlayerGotLiked(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $player->getUser()->getId(),
                statisticName: StatisticEnum::LIKES,
                language: $player->getLanguage(),
            )
        );
    }
}
