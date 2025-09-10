<?php

namespace Mush\Chat\Repository;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\PlayerInfo;

final class InMemoryChannelPlayerRepository implements ChannelPlayerRepositoryInterface
{
    /** @var array<ChannelPlayer> */
    private array $channelPlayers = [];

    public function findAvailablePlayerForPrivateChannel(Channel $channel, Daedalus $daedalus): array
    {
        $availablePlayers = [];
        foreach ($this->channelPlayers as $channelPlayer) {
            if (
                $channelPlayer->getChannel() === $channel
                && $channelPlayer->getParticipant()->getPlayer()?->getDaedalus() === $daedalus
            ) {
                $availablePlayers[] = $channelPlayer;
            }
        }

        return $availablePlayers;
    }

    public function save(ChannelPlayer $channelPlayer): void
    {
        $id = crc32(serialize($channelPlayer));
        (new \ReflectionProperty($channelPlayer, 'id'))->setValue($channelPlayer, $id);

        $this->channelPlayers[$id] = $channelPlayer;
    }

    public function delete(ChannelPlayer $channelPlayer): void
    {
        foreach ($this->channelPlayers as $key => $existingChannelPlayer) {
            if ($existingChannelPlayer === $channelPlayer) {
                unset($this->channelPlayers[$key]);

                return;
            }
        }
    }

    public function findByChannelAndPlayer(Channel $channel, PlayerInfo $playerInfo): ?ChannelPlayer
    {
        foreach ($this->channelPlayers as $channelPlayer) {
            if ($channelPlayer->getChannel()->getId() === $channel->getId()
                && $channelPlayer->getParticipant()->getId() === $playerInfo->getId()
            ) {
                return $channelPlayer;
            }
        }

        return null;
    }

    public function clear(): void
    {
        $this->channelPlayers = [];
    }
}
