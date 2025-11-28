<?php

declare(strict_types=1);

namespace Mush\Notification\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Enum\NotificationEnum;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use WebPush\Notification;

final readonly class DaedalusEventListener
{
    public function __construct(private MessageBusInterface $commandBus) {}

    #[AsEventListener(DaedalusEvent::FULL_DAEDALUS, priority: EventPriorityEnum::LOWEST)]
    public function onDaedalusFilled(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var User $user */
        foreach ($daedalus->getAlivePlayers()->map(static fn (Player $player) => $player->getUser()) as $user) {
            $this->commandBus->dispatch(
                new NotifyUserCommand(
                    notification: NotificationEnum::DAEDALUS_FILLED,
                    user: $user,
                    language: $language,
                    priority: Notification::URGENCY_HIGH,
                    translationParameters: [
                        'user' => $user->getUsername(),
                    ]
                )
            );
        }
    }

    #[AsEventListener(DaedalusEvent::FINISH_DAEDALUS, priority: EventPriorityEnum::LOWEST)]
    public function onDaedalusFinished(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        foreach ($daedalus->getPlayers() as $player) {
            $this->commandBus->dispatch(
                new NotifyUserCommand(
                    notification: NotificationEnum::DAEDALUS_FINISHED,
                    user: $player->getUser(),
                    language: $language,
                    priority: Notification::URGENCY_HIGH,
                    translationParameters: [
                        $player->getLogKey() => $player->getLogName(),
                    ]
                )
            );
        }
    }
}
