<?php

namespace Mush\Chat\Entity\Dto;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Validator\MaxNestedParent;
use Mush\Chat\Validator\MessageParent;
use Mush\Player\Entity\Player;
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

    private ?Player $player;

    private \DateInterval $timeLimit;

    /**
     * @Assert\NotNull
     *
     * @Assert\Type(type="string")
     */
    private string $message;

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function setParent(?Message $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getTimeLimit(): \DateInterval
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(\DateInterval $timeLimit): self
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    public function isVocodedAnnouncement(): bool
    {
        return str_starts_with($this->message, '/neron');
    }
}
