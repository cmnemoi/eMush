<?php

namespace Mush\Communication\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;

/**
 * @ORM\Entity
 * @ORM\Table(name="communication_channel_player")
 */
class ChannelPlayer
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Communication\Entity\Channel", inversedBy="participants")
     */
    private Channel $channel;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player")
     */
    private Player $participant;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParticipant(): Player
    {
        return $this->participant;
    }

    public function setParticipant(Player $participant): self
    {
        $this->participant = $participant;

        return $this;
    }
}
