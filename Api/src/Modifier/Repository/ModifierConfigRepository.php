<?php

namespace Mush\Modifier\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Modifier\Entity\Config\AbstractModifierConfig as ModifierConfig;

/**
 * @template-extends ServiceEntityRepository<ModifierConfig>
 */
class ModifierConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModifierConfig::class);
    }
}
