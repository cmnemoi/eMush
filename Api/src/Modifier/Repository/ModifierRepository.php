<?php

declare(strict_types=1);

namespace Mush\Modifier\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Modifier\Entity\GameModifier;

/**
 * @template-extends ServiceEntityRepository<ModifierActivationRequirement>
 */
final class ModifierRepository extends ServiceEntityRepository implements ModifierRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameModifier::class);
    }

    public function save(GameModifier $gameModifier): void
    {
        try {
            $this->getEntityManager()->persist($gameModifier);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
        }
    }

    public function delete(GameModifier $gameModifier): void
    {
        $this->getEntityManager()->remove($gameModifier);
        $this->getEntityManager()->flush();
    }

    public function wrapInTransaction(callable $callable): void
    {
        $this->getEntityManager()->wrapInTransaction($callable);
    }
}
