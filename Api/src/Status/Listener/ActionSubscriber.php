<?php

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
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
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        if (($actionResult = $event->getActionResult()) === null) {
            throw new \LogicException('actionResult should be provided');
        }

        $actionPaCost = $event->getActionConfig()->getGameVariables()->getValueByName(PlayerVariableEnum::ACTION_POINT);

        if ($actionPaCost > 0) {
            $this->statusService->handleAttempt(
                $player,
                $event->getActionConfig()->getActionName(),
                $actionResult,
                $event->getTags(),
                $event->getTime()
            );
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $actionTarget = $event->getActionTarget();

        $isPlayerLaidDown = $actionTarget instanceof Player && $actionTarget->hasStatus(PlayerStatusEnum::LYING_DOWN);
        $actionShouldRemoveLaidDownStatus = \in_array($event->getAction()->getActionName(), ActionEnum::getForceGetUpActions(), true);

        if ($isPlayerLaidDown && $actionShouldRemoveLaidDownStatus) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::LYING_DOWN,
                holder: $actionTarget,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }
}
