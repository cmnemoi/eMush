<?php

declare(strict_types=1);

namespace Mush\Notification\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Notification\Entity\Subscription;

/**
 * @template-extends ServiceEntityRepository<Subscription>
 */
final class SubscriptionRepository extends ServiceEntityRepository implements SubscriptionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findOneByUserIdAndEndpoint(int $userId, string $endpoint): Subscription
    {
        $subscription = $this->findOneBy(['userId' => $userId, 'endpoint' => $endpoint]);

        return $subscription ?? throw new \RuntimeException('Subscription not found');
    }

    /**
     * @return ArrayCollection<int, Subscription>
     */
    public function findAllByUserId(int $userId): ArrayCollection
    {
        return new ArrayCollection($this->findBy(['userId' => $userId]));
    }

    public function save(Subscription $subscription): void
    {
        $this->getEntityManager()->persist($subscription);
    }

    public function delete(Subscription $subscription): void
    {
        $this->getEntityManager()->remove($subscription);
    }
}
