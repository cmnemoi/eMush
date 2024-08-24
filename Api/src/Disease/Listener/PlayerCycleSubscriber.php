<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerDiseaseServiceInterface $playerDiseaseService,
        private RandomServiceInterface $randomService,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onPlayerNewCycle',
        ];
    }

    public function onPlayerNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getMedicalConditions() as $disease) {
            $this->playerDiseaseService->handleNewCycle($disease, $event->getTime());
        }

        if ($player->hasActiveDisorder() && $player->isLaidDownInShrinkRoom()) {
            $disorder = $this->randomService->getRandomElement($player->getDisorders()->toArray());
            $this->playerDiseaseService->treatDisorder($disorder, $event->getTime());
        }
    }
}
