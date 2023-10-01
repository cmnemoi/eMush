<?php

namespace Mush\Status\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::RESULT_ACTION => 'onResultAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        if (($actionResult = $event->getActionResult()) === null) {
            throw new \LogicException('actionResult should be provided');
        }

        $actionPaCost = $event->getAction()->getGameVariables()->getValueByName(PlayerVariableEnum::ACTION_POINT);

        if ($actionPaCost > 0) {
            $this->statusService->handleAttempt(
                $player,
                $event->getAction()->getActionName(),
                $actionResult,
                $event->getTags(),
                $event->getTime()
            );
        }
    }
}
