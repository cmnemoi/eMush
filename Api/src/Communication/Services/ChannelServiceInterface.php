<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Player\Entity\Player;

interface ChannelServiceInterface
{
    public function getPlayerChannels(Player $player): Collection;

    public function createPrivateChannel(Player $player): Channel;

    public function invitePlayer(Player $player, Channel $channel): Channel;

    public function exitChannel(Player $player, Channel $channel): Channel;
}