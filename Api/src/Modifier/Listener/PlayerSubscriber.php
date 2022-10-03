<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Service\ModifierService;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Game\Service\EventServiceInterface;
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
            PlayerEvent::DEATH_PLAYER => 'onPlayerDeath'
        ];
    }

    public function onPlayerDeath(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->playerLeaveRoom($player);
    }

    public function playerLeaveRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getReach() === ModifierReachEnum::PLACE) {
                    $this->modifierService->deleteModifier($modifierConfig, $place);
                }
            }
        }
    }
}
