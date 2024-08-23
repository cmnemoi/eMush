<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(PlayerDiseaseServiceInterface $playerDiseaseService)
    {
        $this->playerDiseaseService = $playerDiseaseService;
    }

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

        $disorder = $player->getOldestDisorder();
        if ($disorder->isTreatedByAShrink()) {
            $this->playerDiseaseService->treatDisorder($disorder, $event->getTime());
        }
    }
}
