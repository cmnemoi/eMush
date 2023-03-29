<?php

namespace Mush\Status\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\Status;
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
            ActionVariableEvent::ROLL_PERCENTAGE_DIRTY => 'onRollPercentageDirty',
        ];
    }


    public function onRollPercentageDirty(ActionVariableEvent $event): void
    {
        $isDirty = $this->randomService->isSuccessful($event->getQuantity());
        $tags = $event->getTags();

        if ($isDirty) {
            $statusEvent = new StatusEvent(
                PlayerStatusEnum::DIRTY,
                $event->getPlayer(),
                $tags,
                $event->getTime()
            );

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
        }
    }
}
