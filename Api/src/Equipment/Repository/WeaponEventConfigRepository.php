<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\Config\WeaponEventConfig;

/**
 * @template-extends ServiceEntityRepository<WeaponEventConfig>
 */
class WeaponEventConfigRepository extends ServiceEntityRepository implements WeaponEventConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeaponEventConfig::class);
    }

    public function findOneByKey(string $eventKey): WeaponEventConfig
    {
        $config = $this->findOneBy(['name' => $eventKey]);

        if ($config === null) {
            throw new \RuntimeException("{$eventKey} weapon event config not found");
        }

        return $config;
    }
}
