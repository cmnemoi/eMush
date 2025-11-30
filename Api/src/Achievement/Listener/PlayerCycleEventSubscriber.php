<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerCycleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onPlayerNewCycle',
        ];
    }

    public function onPlayerNewCycle(PlayerCycleEvent $event)
    {
        $player = $event->getPlayer();
        $character = $player->isMush() ? CharacterEnum::MUSH : $player->getName();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::getCyclesStatFromCharacterName($character)
        );
    }
}
