<?php

namespace Mush\Game\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\LocalizationConfig;

/**
 * @template-extends ServiceEntityRepository<LocalizationConfig>
 */
class LocalizationConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalizationConfig::class);
    }

    public function findByLanguage(string $language): ?LocalizationConfig
    {
        $queryBuilder = $this->createQueryBuilder('localization_config');

        $queryBuilder
            ->where(
                $queryBuilder->expr()->eq('localization_config.language', ':language')
            )
            ->setParameter('language', $language);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
