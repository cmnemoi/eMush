<?php

declare(strict_types=1);

namespace Mush\Notification\Listener;

use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Enum\NotificationEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use WebPush\Notification;

final readonly class StatusEventListener implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $player = $event->getPlayerStatusHolderOrDefault();

        if ($player->hasAnyStatuses([PlayerStatusEnum::INACTIVE, PlayerStatusEnum::HIGHLY_INACTIVE])) {
            $this->commandBus->dispatch(
                new NotifyUserCommand(
                    notification: NotificationEnum::INACTIVITY,
                    user: $player->getUser(),
                    language: $player->getLanguage(),
                    priority: Notification::URGENCY_HIGH,
                )
            );
        }
    }
}
