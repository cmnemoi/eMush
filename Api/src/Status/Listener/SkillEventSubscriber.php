<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Skill\Event\SkillAddedToPlayerEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SkillEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private StatusServiceInterface $statusService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillAddedToPlayerEvent::class => 'onSkillAddedToPlayer',
        ];
    }

    public function onSkillAddedToPlayer(SkillAddedToPlayerEvent $event): void
    {
        $skill = $event->getSkill();

        $specialistPointsConfig = $skill->getSpecialistPointConfigOrNull();
        if ($specialistPointsConfig === null) {
            return;
        }

        $this->statusService->createStatusFromConfig(
            statusConfig: $specialistPointsConfig,
            holder: $skill->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }
}
