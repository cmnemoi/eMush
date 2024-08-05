<?php

declare(strict_types=1);

namespace Mush\Modifier\Listener;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $project = $event->getProject();

        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($project->getAllModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $project->getDaedalus(),
                modifierProvider: $project,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }
}
