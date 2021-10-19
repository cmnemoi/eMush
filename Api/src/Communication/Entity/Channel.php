<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

/**
 * @ORM\Entity
 * @ORM\Table(name="communication_channel")
 */
class Channel
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $scope = ChannelScopeEnum::PUBLIC;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Communication\Entity\ChannelPlayer", mappedBy="channel")
     */
    private Collection $participants;

    /**
     * @ORM\OneToMany  (targetEntity="Mush\Communication\Entity\Message", mappedBy="channel", cascade={"remove"})
     */
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

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return static
     */
    public function setScope(string $scope): self
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

    public function isPlayerParticipant(Player $player): bool
    {
        return !$this->getParticipants()->filter(fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $player))->isEmpty();
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
