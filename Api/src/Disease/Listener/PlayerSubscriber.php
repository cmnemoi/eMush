<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(PlayerDiseaseServiceInterface $playerDiseaseService)
    {
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CYCLE_DISEASE => 'onCycleDisease',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onCycleDisease(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->hasStatus(PlayerStatusEnum::DEMORALIZED) || $player->hasStatus(PlayerStatusEnum::SUICIDAL)) {
            $cause = DiseaseCauseEnum::CYCLE_LOW_MORALE;
        } else {
            $cause = DiseaseCauseEnum::CYCLE;
        }
        $this->playerDiseaseService->handleDiseaseForCause($cause, $player);
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $playersInRoom = $event->getPlace()->getPlayers()->getPlayerAlive();

        foreach ($playersInRoom as $player) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $player);
        }
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::INFECTION, $player);
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $characterConfig = $player->getCharacterConfig();
        $reason = $event->getReason();

        $initDiseases = $characterConfig->getInitDiseases();
        foreach ($initDiseases as $diseaseName) {
            $this->playerDiseaseService->createDiseaseFromName(
                $diseaseName,
                $player,
                $reason,
            );
        }
    }
}
