<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Communications\Event\NeronVersionUpdatedEvent;
use Mush\Project\Service\FinishRandomDaedalusProjectService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NeronVersionUpdatedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private FinishRandomDaedalusProjectService $finishRandomDaedalusProject) {}

    public static function getSubscribedEvents(): array
    {
        return [
            NeronVersionUpdatedEvent::class => 'onNeronVersionUpdated',
        ];
    }

    public function onNeronVersionUpdated(NeronVersionUpdatedEvent $event): void
    {
        if ($event->majorVersionUpdated) {
            $this->finishRandomDaedalusProject->execute($event->daedalusId);
        }
    }
}
