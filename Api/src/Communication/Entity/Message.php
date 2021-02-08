<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Neron;
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
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Neron")
     */
    private ?Neron $neron = null;

    /**
     * @ORM\OneToMany (targetEntity="Mush\Communication\Entity\Message", mappedBy="parent")
     * @ORM\OrderBy({"createdAt" = "ASC"})
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

    /**
     * @return static
     */
    public function setAuthor(?Player $author): Message
    {
        $this->author = $author;

        return $this;
    }

    public function getNeron(): ?Neron
    {
        return $this->neron;
    }

    public function setNeron(?Neron $neron): Message
    {
        $this->neron = $neron;

        return $this;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    /**
     * @return static
     */
    public function setParent(?Message $parent): Message
    {
        $this->parent = $parent;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return static
     */
    public function setMessage(string $message): Message
    {
        $this->message = $message;

        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @return static
     */
    public function setChannel(Channel $channel): Message
    {
        $this->channel = $channel;

        return $this;
    }

    public function getChild(): Collection
    {
        return $this->child;
    }

    /**
     * @return static
     */
    public function setChild(Collection $child): Message
    {
        $this->child = $child;

        return $this;
    }
}
