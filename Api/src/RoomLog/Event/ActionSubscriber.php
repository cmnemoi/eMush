<?php

namespace Mush\RoomLog\Event;

use Mush\Action\Event\ActionEvent;
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

        if ($actionResult === null || ($log = $actionResult->getLog()) === null) {
            return;
        }

        if (($visibility = $actionResult->getVisibility()) === null) {
            $visibility = VisibilityEnum::PUBLIC;
        }

        $this->roomLogService->createActionLog(
            $log,
            $player->getRoom(),
            $player,
            $actionResult->getTarget(),
            $visibility
        );
    }
}
