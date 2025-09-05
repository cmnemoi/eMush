<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Mush\User\Service\TokenServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UnsubscribeUserCommandHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private TokenServiceInterface $token,
    ) {}

    public function __invoke(UnsubscribeUserCommand $command): void
    {
        $this->subscriptionRepository->delete(
            $this->subscriptionRepository->findOneByUserIdAndEndpoint($this->token->toUserId(), $command->endpoint)
        );
    }
}
