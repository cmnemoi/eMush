<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Mush\User\Entity\BannedIp;

/**
 * @template-extends ServiceEntityRepository<BannedIp>
 */
final class BannedIpRepository extends ServiceEntityRepository implements BannedIpRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedIp::class);
    }

    public function hasAny(array $hashedIps): bool
    {
        return $this->createQueryBuilder('bannedIp')
            ->select('COUNT(bannedIp.id)')
            ->where('bannedIp.value IN (:hashedIps)')
            ->setParameter('hashedIps', $hashedIps)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function save(BannedIp $bannedIp): void
    {
        try {
            $this->getEntityManager()->persist($bannedIp);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
        }
    }
}
