<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\ActionOutputEnum;
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
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public const int OBSERVANT_REVEAL_CHANCE = 25;
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
        $actionName = $event->getActionConfig()->getActionName();
        $actionTarget = $event->getActionTarget();
        $player = $event->getAuthor();

        if ($actionName === ActionEnum::MOVE) {
            /** @var Door $door */
            $door = $actionTarget;
            $this->doesCatMeow($event);
            $this->createExitRoomLog($player, $door);
        }

        match ($actionName) {
            ActionEnum::TAKEOFF => $this->createTakeoffActionLog($event),
            default => null,
        };
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        if ($actionResult === null) {
            throw new \LogicException('$actionResult should not be null');
        }

        $actionLog = $this->roomLogService->createLogFromActionEvent($event);

        if (($actionLog?->getVisibility() === VisibilityEnum::PUBLIC) || ($actionLog?->getVisibility() === VisibilityEnum::REVEALED)) {
            $this->doesCatMeow($event);
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getActionConfig();
        $actionHolder = $event->getActionTarget();
        $player = $event->getAuthor();

        /** @var ActionResult $actionResult */
        $actionResult = $event->getActionResult();

        if ($actionHolder instanceof Player
            && \in_array($action->getActionName()->value, ActionEnum::getForceGetUpActions(), true)
            && $actionHolder->hasStatus(PlayerStatusEnum::LYING_DOWN)
        ) {
            $this->createForceGetUpLog($actionHolder);
        }

        if ($action->getActionName() === ActionEnum::MOVE) {
            /** @var Door $door */
            $door = $actionHolder;
            $this->createEnterRoomLog($player, $door);
            $this->doesCatMeow($event);
        }

        match ($action->getActionName()) {
            ActionEnum::LAND => $this->createLandActionLog($event),
            default => null,
        };

        $content = $actionResult->getContent();
        if ($content !== null) {
            $this->createContentLog($event, $content);
        }

        if (
            $player->getPlace()->hasAlivePlayerWithSkill(SkillEnum::OBSERVANT)
            && $this->d100Roll->isSuccessful(self::OBSERVANT_REVEAL_CHANCE)
            && ($unnoticedSecretRevealedLog = $this->getUnnoticedSecretRevealedLog($player))
        ) {
            $this->createObservantNoticeSomethingLog($player);
            $this->markLogAsNoticed($unnoticedSecretRevealedLog);
        }
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

    private function createLandActionLog(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            $event->getActionResult() instanceof CriticalSuccess ? ActionLogEnum::LAND_SUCCESS : ActionLogEnum::LAND_NO_PILOT,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [$player->getLogKey() => $player->getLogName()],
            new \DateTime('now')
        );
    }

    private function createEnterRoomLog(Player $player, Door $door): void
    {
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
            new \DateTime('now')
        );
    }

    private function createExitRoomLog(Player $player, Door $door): void
    {
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
            new \DateTime('now')
        );
    }

    private function createContentLog(ActionEvent $event, string $content): void
    {
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            ActionLogEnum::READ_CONTENT,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'actions_log',
            $player,
            [$player->getLogKey() => $player->getLogName(), 'content' => $content],
            new \DateTime('now')
        );
    }

    private function createTakeoffActionLog(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        $this->roomLogService->createLog(
            $event->getActionResult() instanceof CriticalSuccess ? ActionLogEnum::TAKEOFF_SUCCESS : ActionLogEnum::TAKEOFF_NO_PILOT,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [$player->getLogKey() => $player->getLogName()],
            new \DateTime('now')
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

    private function doesCatMeow(ActionEvent $event): void
    {
        if ($event->getActionConfig()->getActionName() === ActionEnum::SHOOT_CAT) {
            if ($event->getActionResultOrThrow()->getResultTag() === ActionOutputEnum::FAIL) {
                $this->roomLogService->createLog(
                    LogEnum::CAT_HISS,
                    $event->getPlace(),
                    VisibilityEnum::PUBLIC,
                    'event_log',
                    null,
                    [LogParameterKeyEnum::ITEM => ItemEnum::SCHRODINGER],
                    new \DateTime()
                );
            }

            return;
        }
        if ($event->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER)) {
            if ($this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE)) {
                $this->createCatMeowLog($event);
            }
        }
        foreach ($event->getPlace()->getAlivePlayers() as $playerInRoom) {
            if ($playerInRoom->hasEquipmentByName(ItemEnum::SCHRODINGER)) {
                if ($this->d100Roll->isSuccessful(self::CAT_MEOW_CHANCE)) {
                    $this->createCatMeowLog($event);
                }

                break;
            }
        }
    }

    private function createCatMeowLog(ActionEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::CAT_MEOW,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            [LogParameterKeyEnum::ITEM => ItemEnum::SCHRODINGER],
            new \DateTime()
        );
    }
}
