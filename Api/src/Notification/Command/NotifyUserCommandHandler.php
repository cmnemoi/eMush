<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Factory\NotificationFactory;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use WebPush\StatusReportInterface;
use WebPush\WebPushService as WebPushServiceInterface;

#[AsMessageHandler]
final readonly class NotifyUserCommandHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private TranslationServiceInterface $translate,
        private WebPushServiceInterface $webPush,
    ) {}

    public function __invoke(NotifyUserCommand $command): void
    {
        $notification = NotificationFactory::createFromCommand($command, $this->translate);

        [$expiredSubscriptions, $validSubscriptions] = $this->subscriptionRepository->findAllByUserId($command->user->getId())
            ->partition(static fn ($_, Subscription $subscription) => $subscription->isExpired());

        $expiredSubscriptions->map(fn (Subscription $subscription) => $this->subscriptionRepository->delete($subscription));

        $validSubscriptions
            ->map(fn (Subscription $subscription) => $this->webPush->send($notification, $subscription))
            ->filter(static fn (StatusReportInterface $report) => $report->isSubscriptionExpired())
            ->map(fn (StatusReportInterface $report) => $this->subscriptionRepository->delete($report->getSubscription()));
    }
}
