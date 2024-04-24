<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;

/**
 * @template-extends ServiceEntityRepository<Daedalus>
 */
class DaedalusRepository extends ServiceEntityRepository implements DaedalusRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Daedalus::class);
    }

    public function clear(): void
    {
        $this->getEntityManager()->getConnection()->executeStatement('DELETE FROM daedalus');
    }

    public function existAvailableDaedalus(): bool
    {
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->groupBy('daedalus')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->having('count(player) < ' . 16)
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY]);

        return \count($qb->getQuery()->getResult()) > 0;
    }

    public function findAvailableDaedalus(string $name): ?Daedalus
    {
        $qb = $this->createQueryBuilder('daedalus');

        $daedalusConfig = $this->createQueryBuilder('daedalusConfig');
        $daedalusConfig
            ->select('count(characterConfig.id)')
            ->from(GameConfig::class, 'config')
            ->leftJoin('config.charactersConfig', 'characterConfig')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->where($qb->expr()->eq('config.id', 'daedalus_info.gameConfig'));

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->andWhere($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->andWhere($qb->expr()->eq('daedalus_info.name', ':name'))
            ->groupBy('daedalus')
            ->having('count(player.id) < (' . $daedalusConfig->getDQL() . ')')
            ->setMaxResults(1)
            ->setParameter('name', $name)
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function save(Daedalus $daedalus): void
    {
        $this->getEntityManager()->persist($daedalus);
        $this->getEntityManager()->flush();
    }

    /**
     * Returns all finished Daedaluses.
     *
     * @param bool $strict If true, only return Daedaluses with a GameStatus of FINISHED. If false, return Daedaluses with a GameStatus of FINISHED or CLOSED.
     *
     * @return Daedalus[]
     */
    public function findFinishedDaedaluses(bool $strict = true): array
    {
        $gameStatusParameter = $strict ? [GameStatusEnum::FINISHED] : [GameStatusEnum::FINISHED, GameStatusEnum::CLOSED];
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->setParameter('gameStatus', $gameStatusParameter);

        return $qb->getQuery()->getResult();
    }

    public function findNonFinishedDaedaluses(): array
    {
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->where($qb->expr()->notIn('daedalus_info.gameStatus', ':gameStatus'))
            ->setParameter('gameStatus', [GameStatusEnum::FINISHED, GameStatusEnum::CLOSED]);

        return $qb->getQuery()->getResult();
    }

    public function findNonFinishedDaedalusesByLanguage(string $language): array
    {
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->leftJoin('daedalus_info.localizationConfig', 'localization_config')
            ->where($qb->expr()->notIn('daedalus_info.gameStatus', ':gameStatus'))
            ->andWhere($qb->expr()->eq('localization_config.language', ':language'))
            ->setParameter('gameStatus', [GameStatusEnum::FINISHED, GameStatusEnum::CLOSED])
            ->setParameter('language', $language);

        return $qb->getQuery()->getResult();
    }

    public function findAllDaedalusesOnCycleChange(): DaedalusCollection
    {
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->where($qb->expr()->in('daedalus.isCycleChange', ':cycleChange'))
            ->setParameter('cycleChange', [true]);

        return new DaedalusCollection($qb->getQuery()->getResult());
    }
}
