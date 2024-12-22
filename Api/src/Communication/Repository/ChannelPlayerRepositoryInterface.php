<?php

namespace Mush\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Daedalus\Entity\Daedalus;

interface ChannelPlayerRepositoryInterface
{
    public function findAvailablePlayerForPrivateChannel(Channel $channel, Daedalus $daedalus): array;

    public function save(ChannelPlayer $channelPlayer): void;

    public function delete(ChannelPlayer $channelPlayer): void;
}
