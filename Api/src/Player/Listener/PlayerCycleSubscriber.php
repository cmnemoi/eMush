<?php

namespace Mush\Player\Listener;

use Mush\Game\Enum\EventEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        if ($event->hasTag(EventEnum::NEW_DAY)) {
            $this->playerService->handleNewDay($player, $event->getTime());
        }

        $this->playerService->handleNewCycle($player, $event->getTime());
    }
}
