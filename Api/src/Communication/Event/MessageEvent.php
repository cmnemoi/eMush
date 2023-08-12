<?php

namespace Mush\Communication\Event;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

class MessageEvent extends AbstractGameEvent
{
    public const NEW_MESSAGE = 'new_message';
    public const READ_MESSAGE = 'read_message';

    private Message $message;

    public function __construct(
        Message $message,
        ?Player $author,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($tags, $time);
        $this->message = $message;
        $this->author = $author;
    }

    public function setMessage(Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getChannel(): Channel
    {
        return $this->message->getChannel();
    }
}
