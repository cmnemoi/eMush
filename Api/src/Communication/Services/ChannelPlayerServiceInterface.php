<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Player\Entity\Player;

interface ChannelPlayerServiceInterface
{
    public function addPlayer(Player $player, Channel $channel): ChannelPlayer;

    public function removePlayer(Player $player, Channel $channel): bool;
}
