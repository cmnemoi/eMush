<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public const int CAT_MEOW_CHANCE = 10;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private PlayerRepositoryInterface $playerRepository,
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
            ActionEnum::MOVE => $this->tryToCreatePetNoises($event),
            ActionEnum::TAKEOFF => $this->createTakeoffActionLog($event),
            default => null,
        };
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionLog = $this->roomLogService->createLogFromActionEvent($event);

        if ($actionLog?->isPublicOrRevealed()) {
            $this->tryToCreatePetNoises($event);
        }

        if ($event->getActionName()->isDetectedByMycoAlarm()) {
            $this->improvePlayerStatisticBasedOnLog($event, $actionLog);

            $this->playerRepository->save($event->getAuthor());
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        match ($event->getActionName()) {
            ActionEnum::LAND => $this->createLandActionLog($event),
            ActionEnum::MOVE => $this->tryToCreatePetNoises($event),
            ActionEnum::CONSUME, ActionEnum::CONSUME_DRUG => $this->handleMushConsumeLog($event),
            default => null,
        };

        $this->handlePlayerWakeUpLog($event);
        $this->handleContentLog($event);
        $this->handleMycoAlarmLog($event);
    }

    private function tryToCreatePetNoises(ActionEvent $event): void
    {
        $target = $event->getActionTarget();

        $this->tryToCreateCatNoises($event, $target);
        $this->tryToCreateChickenNoises($event, $target);
        $this->tryToCreateSkinnerNoises($event, $target);
    }

    private function createMushConsumeLog(Player $player, string $log = LogEnum::CONSUME_MUSH): void
    {
        $this->roomLogService->createLog(
            $log,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            [$player->getLogKey() => $player->getLogName()],
            new \DateTime()
        );
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

    private function handleMushConsumeLog(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        if ($event->hasTag(ActionEvent::HUMAN_BECAME_MUSH_DURING_CONSUME)) {
            $this->createMushConsumeLog($player, LogEnum::CONSUME_HUMAN_BECAME_MUSH);

            return;
        }

        if ($player->isMush()) {
            $this->createMushConsumeLog($player);
        }
    }

    private function handlePlayerWakeUpLog(ActionEvent $event): void
    {
        if ($event->shouldMakePlayerWakeUp()) {
            $player = $event->getPlayerActionTargetOrThrow();
            $this->createForceGetUpLog($player);
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
                $player->getLogKey() => $player->getAnonymousKeyOrLogName(),
                ...$this->getPatrolShipLogParameters($player, $patrolShip),
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
                $player->getLogKey() => $player->getAnonymousKeyOrLogName(),
                ...$this->getPatrolShipLogParameters($player, $patrolShip),
            ],
            $event->getTime()
        );
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

    private function tryToCreateCatNoises(ActionEvent $event, ?LogParameterInterface $target): void
    {
        // noise for when the cat is targeted
        if ($target instanceof GameItem && $target->isSchrodinger()
            && ($this->shotAtPetAndFailed($event) || $this->curePetAndFailed($event))) {
            $this->createCatHissLog($event);

            return;
        }
        // noise from when he is just in the room

        if ($this->schrodingerInRoomOrPlayerInventory($event) && $this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE)) {
            $this->createCatMeowLog($event);
        }
        if ($this->pavlovInRoom($event) && $this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE * 2)) {
            $this->createDogBarkLog($event);
        }
    }

    private function tryToCreateChickenNoises(ActionEvent $event, ?LogParameterInterface $target): void
    {
        if (!$target instanceof GameItem || !$target->isSpaceChicken()) {
            return;
        }
        if ($this->shotAtPetAndFailed($event) || $this->curePetAndFailed($event)) {
            $this->createChickenSquawkLog($event);
        }
    }

    private function tryToCreateSkinnerNoises(ActionEvent $event, ?LogParameterInterface $target): void
    {
        if (!$target instanceof GameItem || $target->getName() !== ItemEnum::TREASURE_HUNT_PET) {
            return;
        }
        if ($this->shotAtPetAndFailed($event) || $this->curePetAndFailed($event)) {
            $this->createSkinnerLog($event);
        }
    }

    private function shotAtPetAndFailed(ActionEvent $event): bool
    {
        return $event->getActionConfig()->getActionName() === ActionEnum::SHOOT_EQUIPMENT && $event->getActionResultOrThrow()->isAFail();
    }

    private function shotAtPetAndSucceeded(ActionEvent $event): bool
    {
        return $event->getActionConfig()->getActionName() === ActionEnum::SHOOT_EQUIPMENT && $event->getActionResultOrThrow()->isASuccess();
    }

    private function curePetAndFailed(ActionEvent $event): bool
    {
        return $event->getActionConfig()->getActionName() === ActionEnum::CURE_PET && $event->getActionResultOrThrow()->isAFail();
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

    private function createChickenSquawkLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::CHICKEN_SQUAWK,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $event->getAuthor(),
            [LogParameterKeyEnum::ITEM => ItemEnum::TREASURE_HUNT_SPACE_CHICKEN],
            $event->getTime()
        );
    }

    private function createSkinnerLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::BABY_SKINNER_YELP,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $event->getAuthor(),
            [LogParameterKeyEnum::ITEM => ItemEnum::TREASURE_HUNT_PET],
            $event->getTime()
        );
    }

    private function improvePlayerStatisticBasedOnLog(ActionEvent $event, ?RoomLog $actionLog): void
    {
        $action = $event->getActionName();

        if ($action === ActionEnum::GO_BERSERK) {
            return;
        }

        $place = $event->getPlace();
        $statistic = $event->getAuthor()->getPlayerInfo()->getStatistics();

        if ($place->hasOperationalEquipmentByName(ItemEnum::MYCO_ALARM)
            && !$place->hasStatus(PlaceStatusEnum::DELOGGED->toString())) {
            $statistic->incrementUnstealthActionsTaken();

            return;
        }

        if ($action === ActionEnum::CONVERT_PET) {
            $statistic->incrementStealthActionsTaken();

            return;
        }

        match ($actionLog?->getVisibility()) {
            VisibilityEnum::PUBLIC => throw new \LogicException('Public actions should be handled manually'),
            VisibilityEnum::REVEALED => $statistic->incrementUnstealthActionsTaken(),
            default => $statistic->incrementStealthActionsTaken(),
        };
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
