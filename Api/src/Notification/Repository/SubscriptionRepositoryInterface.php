<?php

declare(strict_types=1);

namespace Mush\Notification\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Notification\Entity\Subscription;

interface SubscriptionRepositoryInterface
{
    public function findOneByUserIdAndEndpoint(int $userId, string $endpoint): Subscription;

    /**
     * @return ArrayCollection<int, Subscription>
     */
    public function findAllByUserId(int $userId): ArrayCollection;

    public function save(Subscription $subscription): void;

    public function delete(Subscription $subscription): void;
}
