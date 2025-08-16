<?php

namespace Mush\Chat\Repository;

use Doctrine\Common\Collections\Collection;
use Mush\Chat\Entity\Channel;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

interface ChannelRepositoryInterface
{
    public function findByPlayer(PlayerInfo $playerInfo, bool $privateOnly = false): Collection;

    public function findMushChannelByDaedalus(Daedalus $daedalus): Channel;

    public function findFavoritesChannelByPlayer(Player $player): ?Channel;

    public function getNumberOfPlayerPrivateChannels(Player $player): int;

    public function save(Channel $channel): void;

    public function delete(Channel $channel): void;

    public function findOneByDaedalusInfoAndScope(DaedalusInfo $daedalusInfo, string $scope): ?Channel;

    public function findDaedalusPublicChannelOrThrow(Daedalus $daedalus): Channel;

    public function wrapInTransaction(callable $operation): mixed;
}
