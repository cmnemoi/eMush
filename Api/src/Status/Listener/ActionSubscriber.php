<?php

namespace Mush\Status\Listener;

use Mush\Action\Event\ActionEvent;
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
        $actionParameter = $event->getActionParameter();
        $player = $event->getPlayer();

        if (($actionResult = $event->getActionResult()) === null) {
            throw new \LogicException('actionResult should be provided');
        }

        if ($event->getAction()->getSuccessRate() < 100) {
            $this->statusService->handleAttempt($event->getPlayer(), $event->getAction()->getName(), $actionResult);
        }
    }
}
