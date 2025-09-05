<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Mush\Notification\Enum\NotificationEnum;
use Mush\User\Entity\User;
use WebPush\Notification;

final readonly class NotifyUserCommand
{
    public function __construct(
        public readonly NotificationEnum $notification,
        public readonly User $user,
        public readonly string $language,
        public readonly string $priority = Notification::URGENCY_NORMAL,
    ) {}
}
