<?php

namespace Mush\Chat\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;

/**
 * @template-extends ServiceEntityRepository<ChannelPlayer>
 */
final class ChannelPlayerRepository extends ServiceEntityRepository implements ChannelPlayerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelPlayer::class);
    }

    public function findAvailablePlayerForPrivateChannel(Channel $channel, Daedalus $daedalus): array
    {
        $playersWithAvailablePrivateChannel = $this->playersWithAvailablePrivateChannel($daedalus->getId());

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('playerInfo')
            ->from(PlayerInfo::class, 'playerInfo')
            ->join(Player::class, 'player', 'WITH', 'playerInfo.player = player')
            ->where('player.id IN (:playerIds)') // only players with available private channels should be able to join a channel
            ->andWhere($queryBuilder->expr()->notIn(
                'playerInfo.id',
                $this->playersAlreadyInChannelDQLQuery()
            ))
            ->andWhere($queryBuilder->expr()->eq('playerInfo.gameStatus', ':gameStatus'))
            ->setParameter('currentChannel', $channel)
            ->setParameter('playerIds', $playersWithAvailablePrivateChannel)
            ->setParameter('gameStatus', GameStatusEnum::CURRENT); // only alive players should be able to join a channel

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(ChannelPlayer $channelPlayer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($channelPlayer);
        $entityManager->flush();
    }

    public function findByChannelAndPlayer(Channel $channel, PlayerInfo $playerInfo): ?ChannelPlayer
    {
        return $this->findOneBy([
            'channel' => $channel,
            'participant' => $playerInfo,
        ]);
    }

    public function delete(ChannelPlayer $channelPlayer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($channelPlayer);
        $entityManager->flush();
    }

    private function playersWithAvailablePrivateChannel(int $daedalusId): array
    {
        $query = <<<'SQL'
            WITH player_open_channels AS (
                SELECT player.id AS player_id, COUNT(channel.*) AS number_of_open_private_channels
                FROM daedalus
                INNER JOIN player
                    ON player.daedalus_id = daedalus.id
                INNER JOIN communication_channel_player AS channel_player
                    ON channel_player.participant_id = player.id
                INNER JOIN communication_channel channel
                    ON channel.id = channel_player.channel_id
                WHERE daedalus.id = :daedalusId
                AND channel.scope = :scope
                GROUP BY player.id
            ),

            player_max_channels AS (
                SELECT player.id AS player_id, game_variable.max_value AS player_max_private_channels
                FROM daedalus
                INNER JOIN player
                    ON player.daedalus_id = daedalus.id
                INNER JOIN game_variable_collection
                    ON game_variable_collection.id = player.player_variables_id
                INNER JOIN game_variable
                    ON game_variable.game_variable_collection_id = game_variable_collection.id
                WHERE daedalus.id = :daedalusId
                AND game_variable.name = :privateChannels
            )

            SELECT player.id
            FROM player
            LEFT JOIN player_open_channels
            ON player.id = player_open_channels.player_id
            LEFT JOIN player_max_channels
            ON player.id = player_max_channels.player_id
            WHERE COALESCE(player_open_channels.number_of_open_private_channels, 0) < player_max_channels.player_max_private_channels;
        SQL;

        $connection = $this->getEntityManager()->getConnection();

        return $connection->executeQuery(
            sql: $query,
            params: [
                'daedalusId' => $daedalusId,
                'scope' => ChannelScopeEnum::PRIVATE,
                'privateChannels' => PlayerVariableEnum::PRIVATE_CHANNELS,
            ]
        )->fetchFirstColumn();
    }

    private function playersAlreadyInChannelDQLQuery(): string
    {
        $queryBuilder = $this->createQueryBuilder('players_already_in_channel');

        $queryBuilder
            ->select('sub_2_player.id')
            ->join('players_already_in_channel.participant', 'sub_2_player')
            ->where($queryBuilder->expr()->eq('players_already_in_channel.channel', ':currentChannel'));

        return $queryBuilder->getDQL();
    }
}
