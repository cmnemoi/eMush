<?php

namespace Mush\Communication\Entity\Dto;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Validator\MaxNestedParent;
use Mush\Communication\Validator\MessageParent;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateMessage.
 *
 * @MessageParent()
 */
class CreateMessage
{
    /**
     * @MaxNestedParent
     */
    private ?Message $parent = null;

    private Channel $channel;

    /**
     * @Assert\NotNull
     * @Assert\Type(type="string")
     */
    private string $message;

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function setParent(?Message $parent): CreateMessage
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): CreateMessage
    {
        $this->channel = $channel;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): CreateMessage
    {
        $this->message = $message;

        return $this;
    }
}
