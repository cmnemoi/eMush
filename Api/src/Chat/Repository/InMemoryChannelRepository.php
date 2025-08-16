<?php

namespace Mush\Chat\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

final class InMemoryChannelRepository implements ChannelRepositoryInterface
{
    /** @var array<Channel> */
    private array $channels = [];

    public function findByPlayer(PlayerInfo $playerInfo, bool $privateOnly = false): Collection
    {
        if ($privateOnly) {
            return new ArrayCollection($this->findPrivateChannels($playerInfo));
        }

        $channels = array_merge(
            $this->findPublicChannels($playerInfo),
            $this->findPrivateChannels($playerInfo),
            $this->findMushChannels($playerInfo)
        );

        return new ArrayCollection($channels);
    }

    public function findMushChannelByDaedalus(Daedalus $daedalus): Channel
    {
        foreach ($this->channels as $channel) {
            if ($channel->getScope() === ChannelScopeEnum::MUSH && $channel->getDaedalusInfo() === $daedalus->getDaedalusInfo()) {
                return $channel;
            }
        }

        throw new \RuntimeException('Mush channel not found for daedalus');
    }

    public function findFavoritesChannelByPlayer(Player $player): ?Channel
    {
        foreach ($this->channels as $channel) {
            if (
                $channel->getScope() === ChannelScopeEnum::FAVORITES
                && $channel->getParticipants()->contains($player->getPlayerInfo())
            ) {
                return $channel;
            }
        }

        return null;
    }

    public function getNumberOfPlayerPrivateChannels(Player $player): int
    {
        $count = 0;
        foreach ($this->channels as $channel) {
            if (
                $channel->getScope() === ChannelScopeEnum::PRIVATE
                && $channel->getParticipants()->contains($player->getPlayerInfo())
            ) {
                ++$count;
            }
        }

        return $count;
    }

    public function save(Channel $channel): void
    {
        $id = crc32(serialize($channel));
        (new \ReflectionProperty($channel, 'id'))->setValue($channel, $id);

        $this->channels[$id] = $channel;
    }

    public function delete(Channel $channel): void
    {
        foreach ($this->channels as $key => $existingChannel) {
            if ($existingChannel === $channel) {
                unset($this->channels[$key]);

                return;
            }
        }
    }

    public function findOneByDaedalusInfoAndScope($daedalusInfo, $scope): ?Channel
    {
        foreach ($this->channels as $channel) {
            if ($channel->getDaedalusInfo() === $daedalusInfo && $channel->getScope() === $scope) {
                return $channel;
            }
        }

        return null;
    }

    public function findDaedalusPublicChannelOrThrow(Daedalus $daedalus): Channel
    {
        foreach ($this->channels as $channel) {
            if ($channel->getScope() === ChannelScopeEnum::PUBLIC && $channel->getDaedalusInfo() === $daedalus->getDaedalusInfo()) {
                return $channel;
            }
        }

        throw new \RuntimeException('Public channel not found for daedalus');
    }

    public function clear(): void
    {
        $this->channels = [];
    }

    public function wrapInTransaction(callable $operation): mixed
    {
        return $operation();
    }

    /**
     * @return array<Channel>
     */
    private function findPrivateChannels(PlayerInfo $playerInfo): array
    {
        $channels = [];
        foreach ($this->channels as $channel) {
            if ($channel->getScope() !== ChannelScopeEnum::PRIVATE) {
                continue;
            }

            $participants = $channel->getParticipants()->map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()
            );

            if ($participants->contains($playerInfo)) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * @return array<Channel>
     */
    private function findPublicChannels(PlayerInfo $playerInfo): array
    {
        $player = $playerInfo->getPlayer();
        if ($player === null) {
            return [];
        }

        $channels = [];
        foreach ($this->channels as $channel) {
            if ($channel->getScope() === ChannelScopeEnum::PUBLIC
                && $channel->getDaedalusInfo() === $player->getDaedalusInfo()
            ) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * @return array<Channel>
     */
    private function findMushChannels(PlayerInfo $playerInfo): array
    {
        $player = $playerInfo->getPlayer();
        if ($player === null || !$player->canAccessMushChannel()) {
            return [];
        }

        $channels = [];
        foreach ($this->channels as $channel) {
            if ($channel->getScope() === ChannelScopeEnum::MUSH
                && $channel->getDaedalusInfo() === $player->getDaedalusInfo()
            ) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }
}
