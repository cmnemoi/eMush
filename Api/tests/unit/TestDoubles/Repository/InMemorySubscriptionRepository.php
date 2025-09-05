<?php

declare(strict_types=1);

namespace Mush\Tests\unit\TestDoubles\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;

final class InMemorySubscriptionRepository implements SubscriptionRepositoryInterface
{
    private array $subscriptions = [];

    public function findOneByUserIdAndEndpoint(int $userId, string $endpoint): Subscription
    {
        $subscription = $this->findOneByUserIdAndEndpointOrNull($userId, $endpoint);
        if ($subscription) {
            return $subscription;
        }

        throw new \RuntimeException("Subscription not found for user id {$userId} and endpoint {$endpoint}");
    }

    /**
     * @return ArrayCollection<int, Subscription>
     */
    public function findAllByUserId(int $userId): ArrayCollection
    {
        $userSubscriptions = [];
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getUserId() === $userId) {
                $userSubscriptions[] = $subscription;
            }
        }

        return new ArrayCollection($userSubscriptions);
    }

    public function save(Subscription $subscription): void
    {
        $this->subscriptions[$subscription->getUserId() . '-' . $subscription->getEndpoint()] = $subscription;
    }

    public function delete(Subscription $subscription): void
    {
        unset($this->subscriptions[$subscription->getUserId() . '-' . $subscription->getEndpoint()]);
    }

    public function findOneByUserIdAndEndpointOrNull(int $userId, string $endpoint): ?Subscription
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getUserId() === $userId && $subscription->getEndpoint() === $endpoint) {
                return $subscription;
            }
        }

        return null;
    }
}
