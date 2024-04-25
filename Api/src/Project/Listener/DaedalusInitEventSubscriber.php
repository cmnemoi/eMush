<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusInitEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase,
        private ProposeNewNeronProjectsUseCase $proposeNewNeronProjectsUseCase
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        // Create all projects
        /** @var ProjectConfig $projectConfig */
        foreach ($daedalus->getProjectConfigs() as $projectConfig) {
            $this->createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $daedalus);
        }

        // Propose some NERON projects
        $this->proposeNewNeronProjectsUseCase->execute($daedalus, number: $daedalus->getNumberOfProjectsByBatch());
    }
}
