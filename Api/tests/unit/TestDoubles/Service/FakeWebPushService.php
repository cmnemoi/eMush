<?php

declare(strict_types=1);

namespace Mush\Tests\unit\TestDoubles\Service;

use WebPush\NotificationInterface;
use WebPush\StatusReport;
use WebPush\StatusReportInterface;
use WebPush\SubscriptionInterface;
use WebPush\WebPushService as WebPushServiceInterface;

final class FakeWebPushService implements WebPushServiceInterface
{
    /** @var array<NotificationInterface> */
    private array $sentNotifications = [];

    public function send(NotificationInterface $notification, SubscriptionInterface $subscription): StatusReportInterface
    {
        $this->sentNotifications[] = $notification;

        return new StatusReport($subscription, $notification, 200, $subscription->getEndpoint(), []);
    }

    /** @return array<NotificationInterface> */
    public function getSentNotifications(): array
    {
        return $this->sentNotifications;
    }
}
