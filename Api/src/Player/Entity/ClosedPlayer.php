<?php

namespace Mush\Player\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    shortName: 'ClosedPlayer',
    description: 'eMush Closed Player',
    normalizationContext: ['groups' => ['closed_player_read']],
)]
#[GetCollection(
    paginationEnabled: false,
    security: 'is_granted("ROLE_USER")',
    filters: ['default.search_filter', 'default.order_filter', 'closedPlayer.search_filter']
)]
#[Get(
    paginationEnabled: false,
    security: 'is_granted("ROLE_USER") and is_granted("DAEDALUS_IS_FINISHED", object)',
)]
class ClosedPlayer
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['closed_player_read'])]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'closedPlayer', targetEntity: PlayerInfo::class)]
    private PlayerInfo $playerInfo;

    #[ORM\ManyToOne(targetEntity: ClosedDaedalus::class, inversedBy: 'players')]
    #[Groups(['closed_player_read'])]
    private ClosedDaedalus $daedalus;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['closed_player_read'])]
    private ?string $message = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['closed_player_read'])]
    private string $endCause = EndCauseEnum::NO_INFIRMERIE;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['closed_player_read'])]
    private int $dayDeath = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['closed_player_read'])]
    private int $cycleDeath = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['closed_player_read'])]
    private int $likes = 0;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups(['closed_player_read'])]
    private bool $isMush = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerInfo(): PlayerInfo
    {
        return $this->playerInfo;
    }

    public function isDaedalusFinished(): bool
    {
        return $this->getClosedDaedalus()->isDaedalusFinished();
    }

    public function setPlayerInfo(PlayerInfo $playerInfo): static
    {
        $this->playerInfo = $playerInfo;

        return $this;
    }

    public function getClosedDaedalus(): ClosedDaedalus
    {
        return $this->daedalus;
    }

    public function setClosedDaedalus(ClosedDaedalus $closedDaedalus): self
    {
        $this->daedalus = $closedDaedalus;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDayDeath(): int
    {
        return $this->dayDeath;
    }

    public function getCycleDeath(): int
    {
        return $this->cycleDeath;
    }

    public function setDayCycleDeath(Daedalus $daedalus): static
    {
        $this->dayDeath = $daedalus->getDay();
        $this->cycleDeath = $daedalus->getCycle();

        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function addLike(): static
    {
        $this->likes = +1;

        return $this;
    }

    public function getEndCause(): string
    {
        return $this->endCause;
    }

    public function setEndCause(string $endCause): static
    {
        $this->endCause = $endCause;

        return $this;
    }

    public function setIsMush(bool $isMush): static
    {
        $this->isMush = $isMush;

        return $this;
    }

    public function isMush(): bool
    {
        return $this->isMush;
    }

    // getter for API Platform serialization
    public function getIsMush(): bool
    {
        return $this->isMush;
    }
}
