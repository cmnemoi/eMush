<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public const int CAT_MEOW_CHANCE = 10;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::RESULT_ACTION => 'onResultAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        match ($event->getActionName()) {
            ActionEnum::MOVE => $this->handleExitActionLog($event),
            ActionEnum::TAKEOFF => $this->createTakeoffActionLog($event),
            default => null,
        };
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionLog = $this->roomLogService->createLogFromActionEvent($event);

        if ($actionLog?->isPublicOrRevealed()) {
            $this->handleCatNoises($event);
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        match ($event->getActionName()) {
            ActionEnum::LAND => $this->handleLandActionLog($event),
            ActionEnum::MOVE => $this->handleEnterActionLog($event),
            default => null,
        };

        $this->handlePlayerWakeUpLog($event);
        $this->handleContentLog($event);
        $this->handleObservantNoticedSomethingLog($event);
        $this->handleMycoAlarmLog($event);
    }

    private function createForceGetUpLog(Player $player): void
    {
        $this->roomLogService->createLog(
            LogEnum::FORCE_GET_UP,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            [$player->getLogKey() => $player->getLogName()],
            new \DateTime()
        );
    }

    private function handleLandActionLog(ActionEvent $event): void
    {
        $this->createLandActionLog($event);
    }

    private function handleContentLog(ActionEvent $event): void
    {
        if ($event->actionResultDoesNotHaveContent()) {
            return;
        }

        $actionResult = $event->getActionResultOrThrow();
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            ActionLogEnum::READ_CONTENT,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'actions_log',
            $player,
            [$player->getLogKey() => $player->getLogName(), 'content' => $actionResult->getContentOrThrow()],
            new \DateTime('now')
        );
    }

    private function handleEnterActionLog(ActionEvent $event): void
    {
        $this->createEnterRoomLog($event);
        $this->handleCatNoises($event);
    }

    private function handleExitActionLog(ActionEvent $event): void
    {
        $this->handleCatNoises($event);
        $this->createExitRoomLog($event);
    }

    private function handlePlayerWakeUpLog(ActionEvent $event): void
    {
        if ($event->shouldMakePlayerWakeUp()) {
            $player = $event->getPlayerActionTargetOrThrow();
            $this->createForceGetUpLog($player);
        }
    }

    private function handleObservantNoticedSomethingLog(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        if ($event->shouldCreateLogNoticedLog($this->d100Roll) && ($unnoticedSecretRevealedLog = $this->getUnnoticedSecretRevealedLog($player))) {
            $this->createObservantNoticeSomethingLog($player);
            $this->markLogAsNoticed($unnoticedSecretRevealedLog);
        }
    }

    private function handleMycoAlarmLog(ActionEvent $event): void
    {
        if ($event->shouldMakeMycoAlarmRing()) {
            $this->createMycoAlarmRingLog($event);
        }
    }

    private function createLandActionLog(ActionEvent $event): void
    {
        $actionResult = $event->getActionResultOrThrow();
        $player = $event->getAuthor();
        $patrolShip = $event->getEquipmentActionTargetOrThrow();

        $this->roomLogService->createLog(
            $actionResult->isACriticalSuccess() ? ActionLogEnum::LAND_SUCCESS : ActionLogEnum::LAND_NO_PILOT,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [
                $player->getLogKey() => $player->getLogName(),
                ...$this->getPatrolShipLogParameters($player, $patrolShip),
            ],
            $event->getTime()
        );
    }

    private function createEnterRoomLog(ActionEvent $event): void
    {
        $door = $event->getDoorActionTargetOrThrow();
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            ActionLogEnum::ENTER_ROOM,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [
                $player->getLogKey() => $player->getLogName(),
                ...$this->getEnterLogParameters($player, $door),
            ],
            $event->getTime()
        );
    }

    private function createExitRoomLog(ActionEvent $event): void
    {
        $door = $event->getDoorActionTargetOrThrow();
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            ActionLogEnum::EXIT_ROOM,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [
                $player->getLogKey() => $player->getLogName(),
                ...$this->getExitLogParameters($player, $door),
            ],
            $event->getTime()
        );
    }

    private function createTakeoffActionLog(ActionEvent $event): void
    {
        $actionResult = $event->getActionResultOrThrow();
        $player = $event->getAuthor();
        $patrolShip = $event->getEquipmentActionTargetOrThrow();

        $this->roomLogService->createLog(
            $actionResult->isACriticalSuccess() ? ActionLogEnum::TAKEOFF_SUCCESS : ActionLogEnum::TAKEOFF_NO_PILOT,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [
                $player->getLogKey() => $player->getLogName(),
                ...$this->getPatrolShipLogParameters($player, $patrolShip),
            ],
            $event->getTime()
        );
    }

    private function getEnterLogParameters(Player $player, Door $door): array
    {
        $placeName = $door->getOtherRoom($player->getPlace())->getLogName();
        $enterLocPrep = $this->translationService->translate(
            "{$placeName}.enter_loc_prep",
            [],
            'rooms',
            $player->getLanguage()
        );

        return [
            'place' => $placeName,
            'enter_loc_prep' => $enterLocPrep,
        ];
    }

    private function getExitLogParameters(Player $player, Door $door): array
    {
        $placeName = $door->getOtherRoom($player->getPlace())->getLogName();
        $exitLocPrep = $this->translationService->translate(
            "{$placeName}.exit_loc_prep",
            [],
            'rooms',
            $player->getLanguage()
        );

        return [
            'place' => $placeName,
            'exit_loc_prep' => $exitLocPrep,
        ];
    }

    private function getPatrolShipLogParameters(Player $player, GameEquipment $patrolShip): array
    {
        $patrolShipLog = $patrolShip->getLogName();
        $patrolShipName = $this->translationService->translate(
            "{$patrolShipLog}.name",
            [],
            'equipments',
            $player->getLanguage()
        );

        return [
            'patrol_ship' => $patrolShipName,
        ];
    }

    private function handleCatNoises(ActionEvent $event): void
    {
        if ($this->shotAtCatAndFailed($event)) {
            $this->createCatHissLog($event);

            return;
        }
        if ($this->shotAtCatAndSucceeded($event)) {
            // A dead cat shouldn't make noise.
            return;
        }
        if ($this->schrodingerInRoomOrPlayerInventory($event) && $this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE)) {
            $this->createCatMeowLog($event);
        }
        if ($this->pavlovInRoom($event) && $this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE * 2)) {
            $this->createDogBarkLog($event);
        }
    }

    private function shotAtCatAndFailed(ActionEvent $event): bool
    {
        return $event->getActionConfig()->getActionName() === ActionEnum::SHOOT_CAT && $event->getActionResultOrThrow()->isAFail();
    }

    private function shotAtCatAndSucceeded(ActionEvent $event): bool
    {
        return $event->getActionConfig()->getActionName() === ActionEnum::SHOOT_CAT && $event->getActionResultOrThrow()->isASuccess();
    }

    private function schrodingerInRoomOrPlayerInventory(ActionEvent $event): bool
    {
        if ($event->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER)) {
            return true;
        }
        foreach ($event->getPlace()->getAlivePlayers() as $playerInRoom) {
            if ($playerInRoom->hasEquipmentByName(ItemEnum::SCHRODINGER)) {
                return true;
            }
        }

        return false;
    }

    private function pavlovInRoom(ActionEvent $event): bool
    {
        return $event->getPlace()->hasEquipmentByName(ItemEnum::PAVLOV);
    }

    private function createDogBarkLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DOG_BARK,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $event->getAuthor(),
            [LogParameterKeyEnum::ITEM => ItemEnum::PAVLOV],
            $event->getTime()
        );
    }

    private function createCatMeowLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::CAT_MEOW,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $event->getAuthor(),
            [LogParameterKeyEnum::ITEM => ItemEnum::SCHRODINGER],
            $event->getTime()
        );
    }

    private function createCatHissLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::CAT_HISS,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $event->getAuthor(),
            [LogParameterKeyEnum::ITEM => ItemEnum::SCHRODINGER],
            $event->getTime()
        );
    }

    private function createObservantNoticeSomethingLog(Player $player): void
    {
        $observantPlayer = $player->getAlivePlayersInRoom()->getOnePlayerWithSkillOrThrow(SkillEnum::OBSERVANT);
        $this->roomLogService->createLog(
            LogEnum::OBSERVANT_NOTICED_SOMETHING,
            $observantPlayer->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $observantPlayer,
            [$observantPlayer->getLogKey() => $observantPlayer->getLogName()],
            new \DateTime()
        );
    }

    private function getUnnoticedSecretRevealedLog(Player $player): ?RoomLog
    {
        $unnoticedSecretRevealedLog = $this->roomLogService->getRoomLog($player)->filter(
            static fn (RoomLog $roomLog) => $roomLog->getBaseVisibility() === VisibilityEnum::SECRET
            && $roomLog->getVisibility() === VisibilityEnum::REVEALED && $roomLog->isUnnoticed()
        )->first() ?: null;

        return $unnoticedSecretRevealedLog;
    }

    private function markLogAsNoticed(RoomLog $roomLog): void
    {
        $roomLog->markAsNoticed();
        $this->roomLogService->persist($roomLog);
    }

    private function createMycoAlarmRingLog(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            LogEnum::MYCO_ALARM_RING,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            [],
            $event->getTime()
        );
    }
}
