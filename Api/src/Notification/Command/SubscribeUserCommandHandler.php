<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Mush\Notification\Entity\Subscription;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Mush\User\Service\TokenServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SubscribeUserCommandHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private TokenServiceInterface $token,
    ) {}

    public function __invoke(SubscribeUserCommand $command): void
    {
        $this->subscriptionRepository->save(
            Subscription::createFromCommand($command)->forUserId($this->token->toUserId())
        );
    }
}
