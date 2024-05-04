<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
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
            ActionEvent::RESULT_ACTION => 'onResultAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        $player = $event->getAuthor();
        $actionSupport = $event->getActionTarget();

        if ($actionResult === null) {
            throw new \LogicException('$actionResult should not be null');
        }

        $actionName = $event->getActionConfig()->getActionName();

        $this->roomLogService->createLogFromActionResult($actionName, $actionResult, $player, $actionSupport, $event->getTime());
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getActionConfig();
        $actionSupport = $event->getActionTarget();
        $player = $event->getAuthor();

        /** @var ActionResult $actionResult */
        $actionResult = $event->getActionResult();

        if ($actionSupport instanceof Player
            && \in_array($action->getActionName(), ActionEnum::getForceGetUpActions(), true)
            && $lyingDownStatus = $actionSupport->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionSupport->removeStatus($lyingDownStatus);
            $this->createForceGetUpLog($actionSupport);
        }

        if ($action->getActionName() === ActionEnum::MOVE) {
            $this->createMoveRoomLog($player, ActionLogEnum::ENTER_ROOM);
        }

        if ($action->getActionName() === ActionEnum::LAND) {
            $this->createLandActionLog($event);
        }

        if ($content = $actionResult->getContent()) {
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

    private function createMoveRoomLog(Player $player, string $type): void
    {
        $this->roomLogService->createLog(
            $type,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [$player->getLogKey() => $player->getLogName()],
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
}
