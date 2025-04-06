<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Skill\Handler\ShrinkHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PlaceCycleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ShrinkHandler $shrinkHandler) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlaceCycleEvent::PLACE_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::LOW],
        ];
    }

    public function onNewCycle(PlaceCycleEvent $event): void
    {
        $this->shrinkHandler->execute($event->getPlace());
    }
}
