<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\CharacterConfig;
use Mush\Player\Entity\Player;

class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findOneByName(string $name): ?Player
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->leftJoin(CharacterConfig::class, 'character_config', Join::WITH, 'user.characterConfig = character_config')
            ->where($qb->expr()->eq('character_config.name', ':name'))
            ->setParameter('name', $name)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
