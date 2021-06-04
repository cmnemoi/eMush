<?php

namespace Mush\Disease\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(DiseaseCauseServiceInterface $diseaseCauseService)
    {
        $this->diseaseCauseService = $diseaseCauseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event)
    {
        $actionResult = $event->getActionResult();
        $equipment = $actionResult !== null ? $actionResult->getTargetEquipment() : null;

        if ($event->getAction()->getName() === ActionEnum::CONSUME && $equipment !== null) {
            $this->diseaseCauseService->handleSpoiledFood($event->getPlayer(), $equipment);
        }
    }
}
