<?php

declare(strict_types=1);

namespace Mush\Triumph\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
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

    public function findAllPersonalTriumphsForPlayerExcept(Player $player, array $except = []): array
    {
        $queryBuilder = $this->createQueryBuilder('triumphConfig');
        $queryBuilder
            ->where('triumphConfig.scope = :scope')
            ->andWhere('triumphConfig.name NOT IN (:exclude)')
            ->setParameter('exclude', $except)
            ->setParameter('scope', CharacterEnum::toPersonalTriumphScope($player->getName())->toString());

        return $queryBuilder->getQuery()->getResult();
    }
}
