<?php

namespace Mush\Status\Listener;

use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private PlayerStatusServiceInterface $playerStatus;

    public function __construct(
        PlayerStatusServiceInterface $playerStatus
    ) {
        $this->playerStatus = $playerStatus;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerModifierEvent::MORAL_POINT_MODIFIER => ['onMoralPointModifier', -10], //Applied after player modification
            PlayerModifierEvent::SATIETY_POINT_MODIFIER => ['onSatietyPointModifier', -10], //Applied after player modification
            PlayerModifierEvent::MOVEMENT_POINT_CONVERSION => ['onMovementPointConversion', 1000], //Applied before any other listener
        ];
    }

    public function onMoralPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        if (!$player->isMush()) {
            $this->playerStatus->handleMoralStatus($player, $playerEvent->getTime());
        }
    }

    public function onSatietyPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        $this->playerStatus->handleSatietyStatus($player, $playerEvent->getTime());
    }

    public function onMovementPointConversion(PlayerModifierEvent $playerEvent): void
    {
        //@TODO incoming in modifier merge
    }
}
