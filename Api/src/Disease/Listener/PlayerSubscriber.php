<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CYCLE_DISEASE => 'onCycleDisease',
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onCycleDisease(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $diseaseRate = $this->modifierService->getEventModifiedValue(
            $player,
            [PlayerEvent::CYCLE_DISEASE],
            ModifierTargetEnum::PERCENTAGE,
            $difficultyConfig->getCycleDiseaseRate(),
            EventEnum::NEW_CYCLE,
            $event->getTime()
        );

        if ($this->randomService->isSuccessful($diseaseRate)) {
            if ($player->hasStatus(PlayerStatusEnum::DEMORALIZED) || $player->hasStatus(PlayerStatusEnum::SUICIDAL)) {
                $cause = DiseaseCauseEnum::CYCLE_LOW_MORALE;
            } else {
                $cause = DiseaseCauseEnum::CYCLE;
            }
            $this->playerDiseaseService->handleDiseaseForCause($cause, $player);
        }
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

        $playerDiseases = $player->getMedicalConditions();
        foreach ($playerDiseases as $playerDisease) {
            $this->playerDiseaseService->handleNewCycle($playerDisease, new \DateTime());
        }
    }
}
