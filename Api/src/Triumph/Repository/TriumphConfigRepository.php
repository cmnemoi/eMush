<?php

declare(strict_types=1);

namespace Mush\Triumph\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphSourceEventInterface;

/**
 * @template-extends ServiceEntityRepository<TriumphConfig>
 */
final class TriumphConfigRepository extends ServiceEntityRepository implements TriumphConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TriumphConfig::class);
    }

    public function findAllByTargetedEvent(TriumphSourceEventInterface $targetedEvent): array
    {
        return $this->findBy(['targetedEvent' => $targetedEvent->getEventName()]);
    }
}
