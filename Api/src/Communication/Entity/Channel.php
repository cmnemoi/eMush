<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\PlayerInfo;

#[ORM\Entity]
#[ORM\Table(name: 'communication_channel')]
class Channel
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $scope = ChannelScopeEnum::PUBLIC;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: ChannelPlayer::class)]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: Message::class, cascade: ['remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalus(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function isPublic(): bool
    {
        return ChannelScopeEnum::PUBLIC === $this->getScope();
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ChannelPlayer $channelPlayer): self
    {
        $this->participants->add($channelPlayer);

        return $this;
    }

    public function isPlayerParticipant(PlayerInfo $playerInfo): bool
    {
        return !$this->getParticipants()->filter(fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $playerInfo))->isEmpty();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): self
    {
        $this->messages = $messages;

        return $this;
    }
}
