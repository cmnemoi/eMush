<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\RollPercentageEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    public const TRAUMA_PROBABILTY = 33;

    private const INFECTION_DISEASE_RATE = 2;
    private const INFECTION_DISEASES_INCUBATING_DELAY = 2;
    private const INFECTION_DISEASES_INCUBATING_LENGTH = 2;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        EventServiceInterface $eventService
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->diseaseCauseService = $diseaseCauseService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CYCLE_DISEASE => 'onCycleDisease',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', 20], // higher priority than Death log
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onCycleDisease(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $rollEvent = new RollPercentageEvent(
            $difficultyConfig->getCycleDiseaseRate(),
            [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
            $event->getTime()
        );

        $rollEvent->setAuthor($player);

        /** @var PlayerVariableEvent $rollEvent */
        $rollEvent = $this->eventService->computeEventModifications($rollEvent, RollPercentageEvent::ROLL_PERCENTAGE);
        $diseaseRate = $rollEvent->getRoundedQuantity();

        if ($this->randomService->isSuccessful($diseaseRate)) {
            if ($player->hasStatus(PlayerStatusEnum::DEMORALIZED) || $player->hasStatus(PlayerStatusEnum::SUICIDAL)) {
                $cause = DiseaseCauseEnum::CYCLE_LOW_MORALE;
            } else {
                $cause = DiseaseCauseEnum::CYCLE;
            }
            $this->diseaseCauseService->handleDiseaseForCause($cause, $player);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {   
        // do not trigger traumas if not a "real death"
        if ($event->hasAnyTag(EndCauseEnum::getGoodEndCauses()->toArray())) {
            return;
        }

        $playersInRoom = $event->getPlace()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player !== $event->getPlayer()
        );

        /** @var Player $player */
        foreach ($playersInRoom as $player) {
            if ($this->randomService->isSuccessful(self::TRAUMA_PROBABILTY) && !$player->isMush()) {
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
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $player);
            }
        }

        // remove disease of the player
        $diseases = $event->getPlayer()->getMedicalConditions();
        foreach ($diseases as $disease) {
            $this->playerDiseaseService->delete($disease);
        }
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->randomService->isSuccessful(self::INFECTION_DISEASE_RATE)) {
            $this->diseaseCauseService->handleDiseaseForCause(
                DiseaseCauseEnum::INFECTION,
                $player,
                self::INFECTION_DISEASES_INCUBATING_DELAY,
                self::INFECTION_DISEASES_INCUBATING_LENGTH
            );
        }
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $characterConfig = $player->getPlayerInfo()->getCharacterConfig();
        $reasons = $event->getTags();

        $initDiseases = $characterConfig->getInitDiseases();
        // get diseases name from initDiseases configs with a closure
        $initDiseases = array_map(
            static function ($diseaseConfig) {
                return $diseaseConfig->getDiseaseName();
            },
            $initDiseases->toArray()
        );

        foreach ($initDiseases as $diseaseName) {
            $this->playerDiseaseService->createDiseaseFromName(
                $diseaseName,
                $player,
                $reasons,
            );
        }
    }
}
