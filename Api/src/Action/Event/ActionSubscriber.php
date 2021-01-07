<?php

namespace Mush\Action\Event;

use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionParameters;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private GetUp $getUpAction;

    public function __construct(
        GetUp $getUp
    ) {
        $this->getUpAction = $getUp;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();

        if ($action->getName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $this->getUpAction->loadParameters($action, $player, new ActionParameters());
            $this->getUpAction->execute();
        }
    }
}
