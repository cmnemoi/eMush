<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Game\Entity\AbstractEventConfig;

/**
 * @template-extends ServiceEntityRepository<WeaponEffectConfig>
 */
class WeaponEffectConfigRepository extends ServiceEntityRepository implements WeaponEffectConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractEventConfig::class);
    }

    public function findAllByWeaponEvent(WeaponEventConfig $weaponEvent): array
    {
        $keys = $weaponEvent->getEffectKeys();
        if (empty($keys)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('weaponEffectConfig');
        $queryBuilder->where($queryBuilder->expr()->in('weaponEffectConfig.name', $keys));

        return $queryBuilder->getQuery()->getResult();
    }
}
