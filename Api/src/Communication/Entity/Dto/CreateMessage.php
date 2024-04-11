<?php

namespace Mush\Communication\Entity\Dto;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Validator\MaxNestedParent;
use Mush\Communication\Validator\MessageParent;
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

    private int $page;

    private int $limit;

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

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
