<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    public const TRAUMA_PROBABILTY = 33;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CYCLE_DISEASE => 'onCycleDisease',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
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

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $playersInRoom = $event->getPlace()->getPlayers()->getPlayerAlive();

        foreach ($playersInRoom as $player) {
            if ($this->randomService->isSuccessful(self::TRAUMA_PROBABILTY)) {
                $characterGender = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';
                $this->roomLogService->createLog(
                    LogEnum::TRAUMA_DISEASE,
                    $event->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    ['character_gender' => $characterGender],
                    $event->getTime()
                );
                $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $player);
            }
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
    }
}
