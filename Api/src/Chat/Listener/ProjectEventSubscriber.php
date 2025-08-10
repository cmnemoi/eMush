<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageModifierServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Project\Event\BricBrocProjectWorkedEvent;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChannelServiceInterface $channelService,
        private MessageModifierServiceInterface $messageModifierService,
        private MessageServiceInterface $messageService,
        private NeronMessageServiceInterface $neronMessageService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BricBrocProjectWorkedEvent::class => 'onBricBrocProjectWorked',
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onBricBrocProjectWorked(BricBrocProjectWorkedEvent $event): void
    {
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::PATCHING_UP,
            daedalus: $event->getDaedalus(),
            parameters: [],
            dateTime: $event->getTime(),
        );
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $project = $event->getProject();

        if ($project->isNeronOrPilgred()) {
            $this->createProjectFinishedNeronAnnouncement($event);
        }

        if ($project->isPheromodem()) {
            $this->addHumanPlayersToMushChannel($event);
        }
    }

    private function createProjectFinishedNeronAnnouncement(ProjectEvent $event): void
    {
        $author = $event->getAuthor();
        $project = $event->getProject();

        $this->neronMessageService->createNeronMessage(
            messageKey: $project->isPilgred() ? NeronMessageEnum::REPAIRED_PILGRED : NeronMessageEnum::NEW_PROJECT,
            daedalus: $event->getDaedalus(),
            parameters: [
                $author->getLogKey() => $author->getLogName(),
                $project->getLogKey() => $project->getLogName(),
            ],
            dateTime: $event->getTime(),
        );
    }

    private function addHumanPlayersToMushChannel(ProjectEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getAlivePlayers()->getHumanPlayer() as $player) {
            $this->channelService->addPlayerToMushChannel($player);
        }
    }
}
