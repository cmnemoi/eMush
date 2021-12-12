<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\EndCauseEnum as EnumEndCauseEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    public const CYCLE_OXYGEN_LOSS = -3;
    public const LOBBY_TIME_LIMIT = 3 * 24 * 60;

    private DaedalusServiceInterface $daedalusService;
    private DaedalusIncidentServiceInterface $daedalusIncidentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DaedalusIncidentServiceInterface $daedalusIncidentService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->daedalusService = $daedalusService;
        $this->daedalusIncidentService = $daedalusIncidentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalus->setCycle($daedalus->getCycle() + 1);

        if ($this->handleDaedalusEnd($daedalus, $event->getTime())) {
            return;
        }

        $this->dispatchCycleChangeEvent($daedalus, $event->getTime());

        $daedalus = $this->handleOxygen($daedalus, $event->getTime());

        $this->daedalusService->persist($daedalus);
    }

    public function onNewDay(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        //reset spore count
        $daedalus->setSpores($daedalus->getDailySpores());

        $this->daedalusService->persist($daedalus);
    }

    private function handleDaedalusEnd(Daedalus $daedalus, \DateTime $time): bool
    {
        if ($daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->isEmpty() &&
            !$daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->isEmpty()
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $daedalus,
                EnumEndCauseEnum::KILLED_BY_NERON,
                $time
            );
            $this->eventDispatcher->dispatch($endDaedalusEvent, DaedalusEvent::END_DAEDALUS);

            return true;
        }

        return false;
    }

    private function handleOxygen(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        //Handle oxygen loss
        $oxygenLoss = self::CYCLE_OXYGEN_LOSS;

        $daedalusEvent = new DaedalusModifierEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            $oxygenLoss,
            EventEnum::NEW_CYCLE,
            $date
        );
        $this->eventDispatcher->dispatch($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        if ($daedalus->getOxygen() === 0) {
            $this->daedalusService->getRandomAsphyxia($daedalus, $date);
        }

        return $daedalus;
    }

    private function dispatchCycleChangeEvent(Daedalus $daedalus, \DateTime $time): void
    {
        $newDay = false;

        $gameConfig = $daedalus->getGameConfig();

        if ($daedalus->getCycle() === $gameConfig->getCyclePerGameDay() + 1) {
            $newDay = true;
            $daedalus->setCycle(1);
            $daedalus->setDay($daedalus->getDay() + 1);
        }

        $this->daedalusIncidentService->handleEquipmentBreak($daedalus, $time);
        $this->daedalusIncidentService->handleDoorBreak($daedalus, $time);
        $this->daedalusIncidentService->handlePanicCrisis($daedalus, $time);
        $this->daedalusIncidentService->handleMetalPlates($daedalus, $time);
        $this->daedalusIncidentService->handleTremorEvents($daedalus, $time);
        $this->daedalusIncidentService->handleElectricArcEvents($daedalus, $time);
        $this->daedalusIncidentService->handleFireEvents($daedalus, $time);

        $daedalus = $this->handleOxygen($daedalus, $time);

        $timeElapsedSinceStart = ($daedalus->getCycle() + $daedalus->getDay() * $gameConfig->getCyclePerGameDay()) * $gameConfig->getCycleLength();
        if ($timeElapsedSinceStart >= self::LOBBY_TIME_LIMIT && $daedalus->getGameStatus() === GameStatusEnum::STARTING) {
            $daedalusEvent = new DaedalusEvent(
                $daedalus,
                EventEnum::NEW_CYCLE,
                $time
            );
            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }

        if ($newDay) {
            $dayEvent = new DaedalusCycleEvent(
                $daedalus,
                EventEnum::NEW_DAY,
                $time
            );
            $this->eventDispatcher->dispatch($dayEvent, DaedalusCycleEvent::DAEDALUS_NEW_DAY);
        }
    }
}
