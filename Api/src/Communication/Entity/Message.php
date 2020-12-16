<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Player\Entity\Player;

/**
 * @ORM\Entity
 */
class Message
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player")
     */
    private ?Player $author = null;

    /**
     * @ORM\OneToMany (targetEntity="Mush\Communication\Entity\Message", mappedBy="parent")
     */
    private Collection $child;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Communication\Entity\Message", inversedBy="child")
     */
    private ?Message $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Communication\Entity\Channel", inversedBy="messages")
     */
    private Channel $channel;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private string $message;

    public function __construct()
    {
        $this->child = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?Player
    {
        return $this->author;
    }

    public function setAuthor(?Player $author): Message
    {
        $this->author = $author;
        return $this;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function setParent(?Message $parent): Message
    {
        $this->parent = $parent;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Message
    {
        $this->message = $message;
        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): Message
    {
        $this->channel = $channel;
        return $this;
    }

    public function getChild(): Collection
    {
        return $this->child;
    }

    public function setChild(Collection $child): Message
    {
        $this->child = $child;
        return $this;
    }
}