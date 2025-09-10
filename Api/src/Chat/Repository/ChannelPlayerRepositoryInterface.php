<?php

namespace Mush\Chat\Repository;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\PlayerInfo;

interface ChannelPlayerRepositoryInterface
{
    public function findAvailablePlayerForPrivateChannel(Channel $channel, Daedalus $daedalus): array;

    public function findByChannelAndPlayer(Channel $channel, PlayerInfo $playerInfo): ?ChannelPlayer;

    public function save(ChannelPlayer $channelPlayer): void;

    public function delete(ChannelPlayer $channelPlayer): void;
}
