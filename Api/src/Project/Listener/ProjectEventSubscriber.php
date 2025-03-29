<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use Mush\Project\UseCase\UnproposeCurrentNeronProjectsUseCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private ProposeNewNeronProjectsUseCase $proposeNewNeronProjects,
        private UnproposeCurrentNeronProjectsUseCase $unproposeCurrentNeronProjects
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_ADVANCED => 'onProjectAdvanced',
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectAdvanced(ProjectEvent $event): void
    {
        if ($event->isNotAboutFinishedProject()) {
            return;
        }

        $this->dispatchFinishedProjectEvent($event);
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        if ($event->isNotAboutNeronProject()) {
            return;
        }
        if ($event->doesNotHaveTag(ProjectEvent::PROJECT_ADVANCED)) {
            return;
        }

        $daedalus = $event->getDaedalus();

        $this->unproposeCurrentNeronProjects->execute($daedalus);
        $this->proposeNewNeronProjects->execute(daedalus: $daedalus, number: $daedalus->getNumberOfProjectsByBatch());
    }

    private function dispatchFinishedProjectEvent(ProjectEvent $event): void
    {
        $finishedProjectEvent = new ProjectEvent(...$event->toArray());
        $finishedProjectEvent->addTag($event->getEventName());

        $this->eventService->callEvent(
            event: $finishedProjectEvent,
            name: ProjectEvent::PROJECT_FINISHED
        );
    }
}
