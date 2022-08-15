<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

interface ChannelServiceInterface
{
    public function canPlayerCommunicate(Player $player): bool;

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection;

    public function getPublicChannel(Daedalus $daedalus): ?Channel;

    public function createPublicChannel(Daedalus $daedalus): Channel;

    public function createPrivateChannel(Player $player): Channel;

    public function invitePlayer(Player $player, Channel $channel): Channel;

    public function getInvitablePlayersToPrivateChannel(Channel $channel): PlayerCollection;

    public function exitChannel(
        Player $player,
        Channel $channel,
        \DateTime $time = null,
        string $reason = CommunicationActionEnum::EXIT
    ): bool;

    public function deleteChannel(Channel $channel): bool;
}
