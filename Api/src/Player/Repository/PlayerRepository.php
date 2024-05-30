<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

/**
 * @template-extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findOneByName(string $name, Daedalus $daedalus): ?Player
    {
        $qb = $this->createQueryBuilder('player');

        $qb
            ->leftJoin(PlayerInfo::class, 'player_info', Join::WITH, 'player.playerInfo = player_info')
            ->leftJoin(CharacterConfig::class, 'character_config', Join::WITH, 'playerInfo.characterConfig = character_config')
            ->where($qb->expr()->eq('character_config.name', ':name'))
            ->andWhere($qb->expr()->eq('player.daedalus', ':daedalus'))
            ->setParameter('name', $name)
            ->setParameter('daedalus', $daedalus);

        $player = $qb->getQuery()->getOneOrNullResult();

        return $player instanceof Player ? $player : null;
    }
}
