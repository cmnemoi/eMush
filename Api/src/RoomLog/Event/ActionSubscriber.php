<?php

namespace Mush\RoomLog\Event;

use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Event\ActionEvent;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        $player = $event->getPlayer();

        $actionName = $event->getAction()->getName();

        if ($actionResult === null) {
            return;
        }

        $logMapping = ActionLogEnum::ACTION_LOGS[$actionName] ?? null;
        $logData = null;
        if ($logMapping) {
            if ($actionResult instanceof Success && isset($logMapping[ActionLogEnum::SUCCESS])) {
                $logData = $logMapping[ActionLogEnum::SUCCESS];
            } elseif ($actionResult instanceof Fail && isset($logMapping[ActionLogEnum::FAIL])) {
                $logData = $logMapping[ActionLogEnum::FAIL];
            }
        }

        if ($logData) {
            $this->roomLogService->createActionLog(
                $logData[ActionLogEnum::VALUE],
                $player->getRoom(),
                $player,
                $actionResult->getTarget(),
                $logData[ActionLogEnum::VISIBILITY]
            );
        }
    }
}
