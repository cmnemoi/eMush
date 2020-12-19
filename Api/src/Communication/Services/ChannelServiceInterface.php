<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

interface ChannelServiceInterface
{
    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection;

    public function createPublicChannel(Daedalus $daedalus): Channel;

    public function createPrivateChannel(Player $player): Channel;

    public function invitePlayerToPublicChannel(Player $player): Channel;

    public function invitePlayer(Player $player, Channel $channel): Channel;

    public function exitChannel(Player $player, Channel $channel): Channel;
}
