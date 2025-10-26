<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ActionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => ['onPostAction', EventPriorityEnum::LOWEST],
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        if ($event->getActionResultOrThrow()->isAFail()) {
            return;
        }

        $author = $event->getAuthor();
        $statisticName = match ($event->getActionName()) {
            ActionEnum::COMMANDER_ORDER => StatisticEnum::GIVE_MISSION,
            ActionEnum::COFFEE => StatisticEnum::COFFEE_TAKEN,
            ActionEnum::SEARCH => StatisticEnum::SUCCEEDED_INSPECTION,
            default => StatisticEnum::NULL,
        };

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $author->getUser()->getId(),
                statisticName: $statisticName,
                language: $author->getLanguage(),
            )
        );
    }
}
