<?php

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionVariableSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE => 'onRollPercentage',
        ];
    }

    public function onRollPercentage(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() === ActionVariableEnum::PERCENTAGE_DIRTINESS) {
            $isDirty = $this->randomService->isSuccessful($event->getRoundedQuantity());
            $tags = $event->getTags();

            if ($isDirty) {
                $statusEvent = new StatusEvent(
                    PlayerStatusEnum::DIRTY,
                    $event->getAuthor(),
                    $tags,
                    $event->getTime()
                );
                $statusEvent->setVisibility(VisibilityEnum::PRIVATE);

                $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
            }
        }
    }
}
