<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Player\Entity\Player;
use function Doctrine\ORM\QueryBuilder;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(Player $player, bool $privateOnly = false): Collection
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'player')
            ->where($queryBuilder->expr()->eq('player', ':player'))
            ->setParameter('player', $player->getId())
        ;

        if ($privateOnly) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('channel.scope', ':private'))
                ->setParameter('private', ChannelScopeEnum::PRIVATE)
            ;
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    public function findAvailablePlayer(): Collection
    {

        $subQuery = $this->createQueryBuilder('channel');
        $subQuery
            ->select('participant.id')
            ->where($subQuery->expr()->eq('channel.scope', ':private'))
            ->andHaving($subQuery->expr()->lt('COUNT(channel.id)', 3))
            ->innerJoin('channel.participants', 'participant')
            ->groupBy('participant.id')
        ;

        $queryBuilder = $this->createQueryBuilder('main');

        $queryBuilder
            ->select('player')
            ->from(Player::class, 'player')
            ->where($queryBuilder->expr()->in(
                'player.id',
                $subQuery->getDQL()
            ))
            ->setParameter('private', ChannelScopeEnum::PRIVATE)
        ;

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
