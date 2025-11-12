<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        if ($event->getProject()->isPilgred()) {
            foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
                $this->commandBus->dispatch(
                    new IncrementUserStatisticCommand(
                        userId: $player->getUser()->getId(),
                        statisticName: StatisticEnum::PILGRED_IS_BACK,
                        language: $event->getLanguage(),
                    )
                );
            }
        }
    }
}
