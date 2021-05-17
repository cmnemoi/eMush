<?php

namespace Mush\Communication\Event;

use Mush\Communication\Entity\Channel;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ChannelEvent extends Event
{
    public const NEW_CHANNEL = 'new_channel';
    public const JOIN_CHANNEL = 'join_channel';
    public const EXIT_CHANNEL = 'exit_channel';

    private Channel $channel;

    private ?Player $player;

    public function __construct(Channel $channel, ?Player $player = null)
    {
        $this->channel = $channel;
        $this->player = $player;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }
}
