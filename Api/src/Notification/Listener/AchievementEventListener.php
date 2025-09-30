<?php

declare(strict_types=1);

namespace Mush\Notification\Listener;

use Mush\Achievement\Event\AchievementUnlockedEvent;
use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Enum\NotificationEnum;
use Mush\User\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AchievementEventListener
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UserRepositoryInterface $userRepository
    ) {}

    #[AsEventListener(AchievementUnlockedEvent::class)]
    public function onAchievementUnlocked(AchievementUnlockedEvent $event): void
    {
        $this->commandBus->dispatch(
            new NotifyUserCommand(
                notification: NotificationEnum::ACHIEVEMENT_UNLOCKED,
                user: $this->userRepository->findOneByIdOrThrow($event->getUserId()),
                language: $event->getLanguage(),
                translationParameters: $event->getTranslationParameters(),
            )
        );
    }
}
