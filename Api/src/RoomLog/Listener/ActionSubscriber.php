<?php

namespace Mush\RoomLog\Listener;

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
        $player = $event->getPlayer();
        $actionParameter = $event->getActionParameter();

        if ($actionResult === null) {
            throw new \LogicException('$actionResult should not be null');
        }

        $actionName = $event->getAction()->getName();

        $this->roomLogService->createLogFromActionResult($actionName, $actionResult, $player, $actionParameter, $event->getTime());
    }

    public function onPostAction(ActionEvent $event)
    {
        $action = $event->getAction();
        $actionParameter = $event->getActionParameter();
        $player = $event->getPlayer();

        if ($actionParameter instanceof Player &&
            in_array($action->getName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $actionParameter->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionParameter->removeStatus($lyingDownStatus);

            $logParameters = [];
            $logParameters[$actionParameter->getLogKey()] = $actionParameter->getLogName();

            $this->roomLogService->createLog(
                LogEnum::FORCE_GET_UP,
                $actionParameter->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $actionParameter,
                $logParameters,
                new \DateTime()
            );
        }

        if ($action->getName() === ActionEnum::MOVE) {
            $this->roomLogService->createLog(
                ActionLogEnum::ENTER_ROOM,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'actions_log',
                $player,
                [$player->getLogKey() => $player->getLogName()],
                new \DateTime('now')
            );
        }
    }
}
