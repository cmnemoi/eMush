<?php

namespace Mush\Action\Event;

use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private ActionServiceInterface $actionService;
    private GetUp $getUpAction;

    public function __construct(
        ActionServiceInterface $actionService,
        GetUp $getUp
    ) {
        $this->actionService = $actionService;
        $this->getUpAction = $getUp;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();

        if ($action->getName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

            if ($getUpAction === null) {
                throw new \LogicException('character do not have get up action');
            }

            $this->getUpAction->loadParameters($getUpAction, $player, new ActionParameters());
            $this->getUpAction->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();

        $this->actionService->handleActionSideEffect($action, $player, new \DateTime());
    }
}
