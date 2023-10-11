<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\DifficultyServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum as EnumEndCauseEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    public const CYCLE_OXYGEN_LOSS = -3;
    public const BASE_DAEDALUS_CYCLE_CHANGE = 'base_daedalus_cycle_change';
    public const LOBBY_TIME_LIMIT = 3 * 24 * 60;

    private DaedalusServiceInterface $daedalusService;
    private DaedalusIncidentServiceInterface $daedalusIncidentService;
    private DifficultyServiceInterface $difficultyService;
    private EventServiceInterface $eventService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DaedalusIncidentServiceInterface $daedalusIncidentService,
        DifficultyServiceInterface $difficultyService,
        EventServiceInterface $eventService
    ) {
        $this->daedalusService = $daedalusService;
        $this->daedalusIncidentService = $daedalusIncidentService;
        $this->difficultyService = $difficultyService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => [
                ['updateDaedalusCycle', 1000],
                ['updateDaedalusDifficulty', EventPriorityEnum::HIGHEST],
                ['triggerEvents', 0],
            ],
        ];
    }

    public function updateDaedalusCycle(DaedalusCycleEvent $event): void
    {
        $tags = $event->getTags();

        $daedalus = $event->getDaedalus();
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        if ($daedalus->getCycle() === $daedalusConfig->getCyclePerGameDay()) {
            $daedalus->setCycle(1);
            $daedalus->setDay($daedalus->getDay() + 1);

            $tags[] = EventEnum::NEW_DAY;
        } else {
            $daedalus->setCycle($daedalus->getCycle() + 1);
        }
        $this->daedalusService->persist($daedalus);

        $event->setTags($tags);
    }

    public function updateDaedalusDifficulty(DaedalusCycleEvent $event): void
    {
        $this->difficultyService->updateDaedalusDifficultyPoints($event->getDaedalus(), DaedalusVariableEnum::HUNTER_POINTS);
    }

    public function triggerEvents(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $time = $event->getTime();

        if ($this->handleDaedalusEnd($daedalus, $time)) {
            return;
        }

        $this->dispatchCycleChangeEvent($daedalus, $time);

        if ($event->hasTag(EventEnum::NEW_DAY)) {
            $this->resetSpores($event);

            $daedalus->setDailyActionSpent(0);
            $this->daedalusService->persist($daedalus);
        }
    }

    private function resetSpores(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $dailySpores = $daedalus->getVariableByName(DaedalusVariableEnum::SPORE)->getMaxValue();

        if ($dailySpores === null) {
            throw new \Exception('daedalus spore gameVariable should have a maximum value');
        }
        // reset spore count
        $daedalus->setSpores($dailySpores);

        $this->daedalusService->persist($daedalus);
    }

    private function handleDaedalusEnd(Daedalus $daedalus, \DateTime $time): bool
    {
        if ($daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->isEmpty()
            && !$daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->isEmpty()
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $daedalus,
                [EnumEndCauseEnum::KILLED_BY_NERON],
                $time
            );
            $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);

            return true;
        }

        return false;
    }

    private function handleOxygen(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        // Handle oxygen loss
        $oxygenLoss = self::CYCLE_OXYGEN_LOSS;

        $daedalusEvent = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            $oxygenLoss,
            [EventEnum::NEW_CYCLE, self::BASE_DAEDALUS_CYCLE_CHANGE],
            $date
        );
        $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);

        if ($daedalus->getOxygen() <= 0) {
            $this->daedalusService->getRandomAsphyxia($daedalus, $date);
        }

        return $daedalus;
    }

    private function dispatchCycleChangeEvent(Daedalus $daedalus, \DateTime $time): void
    {
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        $this->daedalusIncidentService->handleEquipmentBreak($daedalus, $time);
        $this->daedalusIncidentService->handleDoorBreak($daedalus, $time);
        $this->daedalusIncidentService->handlePanicCrisis($daedalus, $time);
        $this->daedalusIncidentService->handleMetalPlates($daedalus, $time);
        $this->daedalusIncidentService->handleTremorEvents($daedalus, $time);
        $this->daedalusIncidentService->handleElectricArcEvents($daedalus, $time);
        $this->daedalusIncidentService->handleFireEvents($daedalus, $time);
        $this->daedalusIncidentService->handleCrewDisease($daedalus, $time);

        $daedalus = $this->handleOxygen($daedalus, $time);

        $timeElapsedSinceStart = ($daedalus->getCycle() + ($daedalus->getDay() - 1) * $daedalusConfig->getCyclePerGameDay()) * $daedalusConfig->getCycleLength();

        if ($timeElapsedSinceStart >= self::LOBBY_TIME_LIMIT && $daedalus->getGameStatus() === GameStatusEnum::STARTING) {
            $daedalusEvent = new DaedalusEvent(
                $daedalus,
                [EventEnum::NEW_CYCLE],
                $time
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        } elseif ($daedalus->getGameStatus() === GameStatusEnum::CURRENT) {
            // Assign Titles every cycle in case of new deaths or inactive players
            $this->daedalusService->assignTitles($daedalus, $time);
        }
    }
}
