<?php

namespace Mush\Communication\Event;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

class MessageEvent extends AbstractGameEvent
{
    public const NEW_MESSAGE = 'new_message';

    private Message $message;

    public function __construct(Message $message, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getChannel(): Channel
    {
        return $this->message->getChannel();
    }

    public function getPlayer(): ?Player
    {
        $author = $this->message->getAuthor();

        if ($author !== null) {
            return $author->getPlayer();
        }

        return null;
    }
}
