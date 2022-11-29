<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Player\Entity\PlayerInfo;

interface ChannelPlayerServiceInterface
{
    public function addPlayer(PlayerInfo $playerInfo, Channel $channel): ChannelPlayer;

    public function removePlayer(PlayerInfo $playerInfo, Channel $channel): bool;
}
