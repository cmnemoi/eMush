<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

/**
 * @template-extends ServiceEntityRepository<Channel>
 */
class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(PlayerInfo $playerInfo, bool $privateOnly = false): Collection
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        $privateChannels = $this->findPrivateChannelsByPlayer($playerInfo);
        if ($privateOnly) {
            return $privateChannels;
        }

        /** @var ArrayCollection<int, Channel> $playerChannels */
        $playerChannels = new ArrayCollection();

        foreach ($privateChannels as $privateChannel) {
            $playerChannels->add($privateChannel);
        }

        $publicChannel = $this->findPublicChannelByDaedalus($player->getDaedalus());
        $playerChannels->add($publicChannel);

        if ($player->isMush()) {
            $mushChannel = $this->findMushChannelByDaedalus($player->getDaedalus());
            $playerChannels->add($mushChannel);
        }

        return $playerChannels;
    }

    public function findMushChannelByDaedalus(Daedalus $daedalus): Channel
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder->where($queryBuilder->expr()->eq('channel.daedalusInfo', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':scope'))
            ->setParameter('scope', ChannelScopeEnum::MUSH)
            ->setParameter('daedalus', $daedalus->getDaedalusInfo());

        $result = $queryBuilder->getQuery()->getResult();

        return $result[0];
    }

    public function findFavoritesChannelByPlayer(Player $player): ?Channel
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':playerInfo'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':scope'))
            ->setParameter('playerInfo', $player->getPlayerInfo()->getId())
            ->setParameter('scope', ChannelScopeEnum::FAVORITES)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    private function findPublicChannelByDaedalus(Daedalus $daedalus): Channel
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder->where($queryBuilder->expr()->eq('channel.daedalusInfo', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':scope'))
            ->setParameter('scope', ChannelScopeEnum::PUBLIC)
            ->setParameter('daedalus', $daedalus->getDaedalusInfo());

        return $queryBuilder->getQuery()->getSingleResult();
    }

    private function findPrivateChannelsByPlayer(PlayerInfo $playerInfo): Collection
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':playerInfo'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':private'))
            ->setParameter('playerInfo', $playerInfo->getId())
            ->setParameter('private', ChannelScopeEnum::PRIVATE)
        ;

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
