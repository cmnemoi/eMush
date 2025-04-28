<?php

declare(strict_types=1);

namespace Mush\MetaGame\Listener;

use Mush\MetaGame\Service\SkinServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private SkinServiceInterface $skinService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $projectConfig = $event->getProject()->getConfig();

        // @var AbstractModifierConfig $modifierConfig
        foreach ($projectConfig->getSkins() as $skin) {
            $this->skinService->applySkinToAllDaedalus($skin, $event->getDaedalus());
        }
    }
}
