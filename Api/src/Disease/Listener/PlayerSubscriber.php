<?php

namespace Mush\Disease\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
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
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    public const TRAUMA_AUTHOR_PROBABILTY = 33;
    public const TRAUMA_WITNESS_PROBABILTY = 5;

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
            PlayerEvent::NEW_PLAYER => ['onNewPlayer', EventPriorityEnum::HIGH],
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
            $this->diseaseCauseService->handleDiseaseForCause($cause, $player, 0, 0, $event->getTime());
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->removeDeadPlayerDiseases($event);

        // Do not apply trauma diseases if player's end cause is not a "real" death
        if ($event->hasAnyTag(EndCauseEnum::getNotDeathEndCauses()->toArray())) {
            return;
        }

        $this->applyTraumaToDeathAuthor($event);
        $this->applyTraumaToDeathWitnesses($event);
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        if ($event->hasAnyTag([PlanetSectorEvent::MUSH_TRAP, DaedalusEvent::FULL_DAEDALUS])) {
            return;
        }

        if ($this->randomService->isSuccessful(self::INFECTION_DISEASE_RATE)) {
            $this->diseaseCauseService->handleDiseaseForCause(
                DiseaseCauseEnum::INFECTION,
                $player,
                self::INFECTION_DISEASES_INCUBATING_DELAY,
                self::INFECTION_DISEASES_INCUBATING_LENGTH,
                $event->getTime()
            );
        }
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $characterConfig = $player->getPlayerInfo()->getCharacterConfig();
        $reasons = $event->getTags();

        $initDiseases = $characterConfig->getInitDiseases()->map(static fn (DiseaseConfig $diseaseConfig) => $diseaseConfig->getDiseaseName());

        foreach ($initDiseases as $diseaseName) {
            $this->playerDiseaseService->createDiseaseFromName(
                $diseaseName,
                $player,
                $reasons,
            );
        }
    }

    private function removeDeadPlayerDiseases(PlayerEvent $event): void
    {
        $diseases = $event->getPlayer()->getMedicalConditions();

        foreach ($diseases as $disease) {
            $this->playerDiseaseService->removePlayerDisease(
                playerDisease: $disease,
                causes: $event->getTags(),
                time: $event->getTime(),
                visibility: VisibilityEnum::HIDDEN
            );
        }
    }

    private function applyTraumaToDeathAuthor(PlayerEvent $event): void
    {
        $author = $event->getAuthor();
        $deadPlayer = $event->getPlayer();

        if (
            $this->randomService->isSuccessful(self::TRAUMA_AUTHOR_PROBABILTY)
            && $author?->isHuman()
            && $author->getPlace()->equals($deadPlayer->getPlace())
            && $author->doesNotHaveSkill(SkillEnum::DETACHED_CREWMEMBER)
        ) {
            $this->roomLogService->createLog(
                logKey: LogEnum::TRAUMA_DISEASE,
                place: $event->getPlace(),
                visibility: VisibilityEnum::PRIVATE,
                type: 'event_log',
                player: $author,
                parameters: ['character_gender' => $author->getGender()],
                dateTime: $event->getTime()
            );
            $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $author, 0, 0, $event->getTime());
        }
    }

    private function applyTraumaToDeathWitnesses(PlayerEvent $event): void
    {
        $playersInRoom = $event->getPlace()->getAlivePlayersExcept($event->getPlayer());
        if ($event->hasAuthor()) {
            $playersInRoom = $playersInRoom->getAllExcept($event->getAuthorOrThrow());
        }

        /** @var Player $player */
        foreach ($playersInRoom as $player) {
            if (
                $this->randomService->isSuccessful(self::TRAUMA_WITNESS_PROBABILTY)
                && $player->isHuman() && $player->doesNotHaveSkill(SkillEnum::DETACHED_CREWMEMBER)
            ) {
                $this->roomLogService->createLog(
                    logKey: LogEnum::TRAUMA_DISEASE,
                    place: $event->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    type: 'event_log',
                    player: $player,
                    parameters: ['character_gender' => $player->getGender()],
                    dateTime: $event->getTime()
                );
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $player, 0, 0, $event->getTime());
            }
        }
    }
}
