<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public function __construct(
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
            $this->createMoveRoomLog($player, ActionLogEnum::EXIT_ROOM, $door);
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

        $this->roomLogService->createLogFromActionEvent($event);
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
            $this->createMoveRoomLog($player, ActionLogEnum::ENTER_ROOM, $door);
        }

        match ($action->getActionName()) {
            ActionEnum::LAND => $this->createLandActionLog($event),
            default => null,
        };

        $content = $actionResult->getContent();
        if ($content !== null) {
            $this->createContentLog($event, $content);
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

    private function createMoveRoomLog(Player $player, string $type, Door $door): void
    {
        $this->roomLogService->createLog(
            $type,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            $this->getMoveLogParameters($player, $type, $door),
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

    private function getMoveLogParameters(Player $player, string $type, Door $door): array
    {
        $placeName = $type === ActionLogEnum::EXIT_ROOM ? $door->getOtherRoom($player->getPlace())->getLogName() : $player->getOldPlaceOrThrow()->getLogName();
        $exitLocPrep = $this->translationService->translate(
            "{$placeName}.loc_prep",
            [],
            'rooms',
            $player->getLanguage()
        );
        $enterLocPrep = $this->translationService->translate(
            "{$placeName}.tracker_loc_prep",
            [],
            'rooms',
            $player->getLanguage()
        );

        return [
            $player->getLogKey() => $player->getLogName(),
            'enter_loc_prep' => $enterLocPrep,
            'exit_loc_prep' => $exitLocPrep,
            'place' => $placeName,
        ];
    }
}
