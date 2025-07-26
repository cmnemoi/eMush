<?php

namespace Mush\Chat\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

/**
 * @template-extends ServiceEntityRepository<Channel>
 */
final class ChannelRepository extends ServiceEntityRepository implements ChannelRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(PlayerInfo $playerInfo, bool $privateOnly = false): Collection
    {
        if ($privateOnly) {
            return $this->findPrivateChannelsByPlayer($playerInfo);
        }

        $player = $playerInfo->getPlayer();

        $rawQuery = <<<'EOD'
            WITH private_channels AS (
                SELECT channel.*
                FROM communication_channel channel
                INNER JOIN communication_channel_player channel_participant ON channel.id = channel_participant.channel_id
                WHERE channel_participant.participant_id = :playerInfo AND channel.scope = 'private'
            ), public_channel AS (
                SELECT channel.*
                FROM communication_channel channel
                WHERE channel.daedalus_info_id = :daedalusInfo AND channel.scope = 'public'
            ), mush_channel AS (
                SELECT channel.*
                FROM communication_channel channel
                WHERE channel.daedalus_info_id = :daedalusInfo AND channel.scope = 'mush'
            ), neron_channel AS (
                SELECT channel.*
                FROM communication_channel channel
                WHERE channel.daedalus_info_id = :daedalusInfo AND channel.scope = 'neron'
                AND EXISTS (
                    SELECT 1
                    FROM communication_channel_player channel_participant
                    WHERE channel_participant.channel_id = channel.id
                    AND channel_participant.participant_id = :playerInfo
                )
            )


            SELECT * FROM private_channels
            UNION ALL
            SELECT * FROM public_channel
            UNION ALL
            SELECT * FROM neron_channel
        EOD;
        $rawQuery .= $player->canAccessMushChannel() ? ' UNION ALL SELECT * FROM mush_channel;' : ';';

        $entityManager = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Channel::class, 'channel');

        $query = $entityManager
            ->createNativeQuery($rawQuery, $rsm)
            ->setParameter('playerInfo', $player->getId())
            ->setParameter('daedalusInfo', $player->getDaedalusInfo());

        return new ArrayCollection($query->getResult());
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
            ->setParameter('scope', ChannelScopeEnum::FAVORITES);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getNumberOfPlayerPrivateChannels(Player $player): int
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->select('COUNT(channel)')
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':playerInfo'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':scope'))
            ->setParameter('playerInfo', $player->getPlayerInfo())
            ->setParameter('scope', ChannelScopeEnum::PRIVATE);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function save(Channel $channel): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($channel);
        $entityManager->flush();
    }

    public function delete(Channel $channel): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($channel);
        $entityManager->flush();
    }

    public function findOneByDaedalusInfoAndScope($daedalusInfo, $scope): ?Channel
    {
        return $this->findOneBy(['daedalusInfo' => $daedalusInfo, 'scope' => $scope]);
    }

    public function findDaedalusPublicChannelOrThrow(Daedalus $daedalus): Channel
    {
        $channel = $this->findOneByDaedalusInfoAndScope($daedalus->getDaedalusInfo(), ChannelScopeEnum::PUBLIC);
        if ($channel === null) {
            throw new \RuntimeException("Daedalus {$daedalus->getId()} does not have a public channel");
        }

        return $channel;
    }

    private function findPrivateChannelsByPlayer(PlayerInfo $playerInfo): Collection
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':playerInfo'))
            ->andWhere($queryBuilder->expr()->eq('channel.scope', ':scope'))
            ->setParameter('playerInfo', $playerInfo->getId())
            ->setParameter('scope', ChannelScopeEnum::PRIVATE);

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
