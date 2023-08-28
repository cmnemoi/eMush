<?php

namespace Mush\Communication\Event;

use Mush\Communication\Entity\Channel;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

class ChannelEvent extends AbstractGameEvent
{
    public const NEW_CHANNEL = 'new_channel';
    public const JOIN_CHANNEL = 'join_channel';
    public const EXIT_CHANNEL = 'exit_channel';
    public const REQUEST_CHANNEL = 'request_channel';

    private Channel $channel;

    public function __construct(Channel $channel, array $tags, \DateTime $time, Player $player = null)
    {
        parent::__construct($tags, $time);
        $this->channel = $channel;
        $this->author = $player;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }
}
