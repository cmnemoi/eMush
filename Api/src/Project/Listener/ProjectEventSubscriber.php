<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use Mush\Project\UseCase\UnproposeCurrentNeronProjectsUseCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private ProposeNewNeronProjectsUseCase $proposeNewNeronProjectsUseCase,
        private UnproposeCurrentNeronProjectsUseCase $unproposeCurrentNeronProjectsUseCase
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

        $this->eventService->callEvent(new ProjectEvent(...$event->toArray()), ProjectEvent::PROJECT_FINISHED);
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        if ($event->isNotAboutNeronProject()) {
            return;
        }

        $daedalus = $event->getDaedalus();

        // first, unpropose all current NERON projects
        $this->unproposeCurrentNeronProjectsUseCase->execute($daedalus);

        // then, propose new NERON projects
        $this->proposeNewNeronProjectsUseCase->execute(daedalus: $daedalus, number: $daedalus->getNumberOfProjectsByBatch());
    }
}
