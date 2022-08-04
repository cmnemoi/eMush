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
}
