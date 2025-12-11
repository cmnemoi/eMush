<?php

namespace Mush\Chat\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChatActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

interface ChannelServiceInterface
{
    public function getPublicChannel(DaedalusInfo $daedalusInfo): ?Channel;

    public function createPublicChannel(DaedalusInfo $daedalusInfo): Channel;

    public function createMushChannel($daedalusInfo): Channel;

    public function getMushChannel(DaedalusInfo $daedalusInfo): ?Channel;

    public function getMushChannelOrThrow(Daedalus $daedalus): Channel;

    public function createPrivateChannel(Player $player): Channel;

    public function invitePlayer(Player $player, Channel $channel): Channel;

    public function addPlayerToMushChannel(Player $player);

    public function removePlayerFromMushChannel(Player $player);

    public function getInvitablePlayersToPrivateChannel(Channel $channel, Player $player): PlayerCollection;

    public function exitChannel(
        Player $player,
        Channel $channel,
        ?\DateTime $time = null,
        string $reason = ChatActionEnum::EXIT
    ): bool;

    public function deleteChannel(Channel $channel): bool;

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection;

    public function canPlayerCommunicate(Player $player): bool;

    public function canPlayerWhisper(Player $player, Player $otherPlayer): bool;

    public function canPlayerWhisperInChannel(Channel $channel, Player $player): bool;

    public function updatePlayerPrivateChannels(Player $player, string $reason, \DateTime $time): void;

    public function getPiratedPlayer(Player $player): ?Player;

    public function getPiratedChannels(Player $piratedPlayer): Collection;

    public function addPlayer(PlayerInfo $playerInfo, Channel $channel): void;

    public function removePlayer(PlayerInfo $playerInfo, Channel $channel): bool;

    public function markChannelAsReadForPlayer(Channel $channel, Player $player): void;

    public function markTipsChannelAsReadForPlayer(Channel $tipsChannel, Player $player): void;
}
