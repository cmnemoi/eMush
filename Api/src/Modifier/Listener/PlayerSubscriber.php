<?php

namespace Mush\Modifier\Listener;

use Mush\Modifier\Service\ModifierService;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ModifierService $modifierService;

    public function __construct(
        ModifierService $modifierService
    ) {
        $this->modifierService = $modifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onPlayerDeath',
        ];
    }

    public function onPlayerDeath(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->modifierService->playerLeaveRoom($player);
    }
}
