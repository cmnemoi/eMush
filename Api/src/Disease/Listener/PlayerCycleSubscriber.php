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

        // first, decrement a random active disease which heals at cycle change
        if ($player->hasActiveDiseaseHealingAtCycleChange()) {
            $playerDisease = $this->randomService->getRandomElement($player->getActiveDiseasesHealingAtCycleChange()->toArray());
            $playerDisease->decrementDiseasePoints();
            $this->playerDiseaseService->persist($playerDisease);
        }

        // then, treat a random disorder by a shrink
        if ($player->hasActiveDisorder() && $player->isLaidDownInShrinkRoom()) {
            $disorder = $this->randomService->getRandomElement($player->getActiveDisorders()->toArray());
            $this->playerDiseaseService->treatDisorder($disorder, $event->getTime());
        }

        // finally, handle all player diseases as a whole
        foreach ($player->getMedicalConditions() as $playerDisease) {
            $this->playerDiseaseService->handleNewCycle($playerDisease, $event->getTime());
        }
    }
}
