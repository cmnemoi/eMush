<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Event\ActionEvent;
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

        if ($actionResult === null) {
            return;
        }

        $actionName = $event->getAction()->getName();

        $this->roomLogService->createLogFromActionResult($actionName, $actionResult, $player);
    }
}
