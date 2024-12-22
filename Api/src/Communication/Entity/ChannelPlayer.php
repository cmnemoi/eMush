<?php

namespace Mush\Communication\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\PlayerInfo;

#[ORM\Entity]
#[ORM\Table(name: 'communication_channel_player')]
class ChannelPlayer
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'participants')]
    private Channel $channel;

    #[ORM\ManyToOne(targetEntity: PlayerInfo::class)]
    private PlayerInfo $participant;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $leftChannel = false;

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
        $channel->addParticipant($this);

        return $this;
    }

    public function getParticipant(): PlayerInfo
    {
        return $this->participant;
    }

    public function setParticipant(PlayerInfo $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function hasLeftChannel(): bool
    {
        return $this->leftChannel;
    }

    public function leaveChannel(): self
    {
        $this->leftChannel = true;

        return $this;
    }

    public function enterChannel(): self
    {
        $this->leftChannel = false;

        return $this;
    }
}
