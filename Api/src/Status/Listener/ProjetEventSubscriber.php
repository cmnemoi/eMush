<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Project\Event\ProjectEvent;
use Mush\Project\Exception\ProjetEventShouldHaveAnAuthorException;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjetEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_ADVANCED => 'onProjectAdvanced',
        ];
    }

    public function onProjectAdvanced(ProjectEvent $event): void
    {
        $this->incrementPlayerParticipationsToProject($event);
    }

    private function incrementPlayerParticipationsToProject(ProjectEvent $event): void
    {
        $author = $event->getAuthor();
        $project = $event->getProject();

        if (!$author) {
            throw new ProjetEventShouldHaveAnAuthorException($project->getName());
        }

        /** @var ChargeStatus $projectParticipationsStatus */
        $projectParticipationsStatus = $author->getStatusByNameAndTarget(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $project);
        if ($projectParticipationsStatus) {
            $this->statusService->updateCharge(
                chargeStatus: $projectParticipationsStatus,
                delta: 1,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        } else {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::PROJECT_PARTICIPATIONS,
                holder: $author,
                tags: $event->getTags(),
                time: $event->getTime(),
                target: $project,
            );
        }
    }
}
