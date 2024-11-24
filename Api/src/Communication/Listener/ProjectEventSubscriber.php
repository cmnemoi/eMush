<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\MessageModificationEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageModifierServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
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

        if ($project->isNeronProject() || $project->isPilgred()) {
            $this->createProjectFinishedNeronAnnouncement($event);
        }

        if ($project->isPatulineScrambler()) {
            $this->scrambleMushChannelMessages($event);
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

    private function scrambleMushChannelMessages(ProjectEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $mushChannel = $this->channelService->getMushChannelOrThrow($daedalus);
        $mushChannelMessages = $this->messageService->getChannelMessages(
            player: null,
            channel: $mushChannel,
            timeLimit: new \DateInterval('P1Y')
        );

        foreach ($mushChannelMessages as $message) {
            $message = $this->messageModifierService->applyModifierEffects(
                message: $message,
                player: null,
                effectName: MessageModificationEnum::PATULINE_SCRAMBLER_MODIFICATION,
            );
            $this->messageService->save($message);
        }
    }
}
