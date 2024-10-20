<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $statusName = $event->getStatusName();
        $place = $event->getPlace();

        $logMap = StatusEventLogEnum::STATUS_EVENT_LOGS[StatusEvent::STATUS_APPLIED];
        if (isset($logMap[$statusName])) {
            $logKey = $logMap[$statusName];
        } else {
            return;
        }

        $this->createEventLog($logKey, $event);

        if (
            $holder instanceof Door
            && $statusName === EquipmentStatusEnum::BROKEN
            && $place !== null
        ) {
            $this->roomLogService->createLog(
                $logKey,
                $holder->getOtherRoom($place),
                $event->getVisibility(),
                'event_log',
                null,
                $event->getLogParameters(),
                $event->getTime()
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();
        match ($statusName) {
            PlaceStatusEnum::DELOGGED->toString() => $this->handleDeloggedPlace(event: $event),
            PlayerStatusEnum::MUSH => $this->handleMushStatusRemoved(event: $event),
            default => null,
        };

        $logMap = StatusEventLogEnum::STATUS_EVENT_LOGS[StatusEvent::STATUS_REMOVED];

        if (isset($logMap[$statusName])) {
            $logKey = $logMap[$statusName];
        } else {
            return;
        }

        $this->createEventLog($logKey, $event);
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof ChargeStatusEvent) {
            return;
        }

        $delta = $event->getRoundedQuantity();
        if ($delta === 0) {
            return;
        }

        // add special logs
        $specialLogMap = StatusEventLogEnum::STATUS_EVENT_LOGS[VariableEventInterface::CHANGE_VARIABLE];
        $specialLogKey = $event->mapLog($specialLogMap[StatusEventLogEnum::VALUE]);

        if ($specialLogKey !== null) {
            $logVisibility = $event->mapLog($specialLogMap[StatusEventLogEnum::VISIBILITY]);

            $this->createEventLog($specialLogKey, $event, $logVisibility);
        }

        $gainOrLoss = $delta > 0 ? StatusEventLogEnum::GAIN : StatusEventLogEnum::LOSS;
        $logMap = StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS[$gainOrLoss];

        if (\array_key_exists($event->getStatusName(), $logMap[StatusEventLogEnum::VALUE])) {
            $logKey = $logMap[StatusEventLogEnum::VALUE][$event->getStatusName()];
            $visibility = $logMap[StatusEventLogEnum::VISIBILITY][$event->getStatusName()] ?? null;
            $this->createEventLog($logKey, $event, $visibility);
        }
    }

    private function createEventLog(string $logKey, StatusEvent $event, ?string $visibility = null): void
    {
        $player = $event->getStatusHolder();
        $place = $event->getPlace();

        if ($place === null) {
            return;
        }

        if (!$player instanceof Player) {
            $player = null;
        }

        $logParameters = $event->getLogParameters();
        if ($event->hasTag(PlaceStatusEnum::CEASEFIRE->toString())) {
            $logParameters['new_cycle'] = $event->hasTag(EventEnum::NEW_CYCLE) ? 'true' : 'false';
        }

        $this->roomLogService->createLog(
            $logKey,
            $place,
            $visibility ?? $event->getVisibility(),
            'event_log',
            $player,
            $logParameters,
            $event->getTime()
        );
    }

    private function handleDeloggedPlace(StatusEvent $event): void
    {
        $this->hideDeloggedLog($event);
        $this->displayCurrentCycleLogs($event);
    }

    private function hideDeloggedLog(StatusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $deloggedLog = $this->roomLogService->findOneByPlaceAndDaedalusDateOrThrow(
            logKey: LogEnum::DELOGGED,
            place: $event->getPlaceOrThrow(),
            date: $daedalus->getPreviousGameDate(),
        );

        $deloggedLog->hide();
        $this->roomLogService->persist($deloggedLog);
    }

    private function displayCurrentCycleLogs(StatusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $currentCycleLogs = $this->roomLogService->findAllByDaedalusPlaceAndCycle(
            daedalus: $daedalus,
            place: $event->getPlaceOrThrow(),
            cycle: $daedalus->getCycle()
        );

        foreach ($currentCycleLogs as $log) {
            $log->resetVisibility();
            $this->roomLogService->persist($log);
        }
    }

    private function handleMushStatusRemoved(StatusEvent $event)
    {
        if($event->hasTag(ActionEnum::CURE->value))
           $this->handlePlayerVaccinated($event);
    }

    private function handlePlayerVaccinated(StatusEvent $event){
        $player = $event->getPlayerStatusHolder();
        $this->roomLogService->createLog(
            'player_vaccinated',
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            [],
            $event->getTime()
        );
    }

}
