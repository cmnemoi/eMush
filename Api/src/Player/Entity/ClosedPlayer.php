<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Enum\EndCauseEnum;

#[ORM\Entity]
class ClosedPlayer
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'closedPlayer', targetEntity: PlayerInfo::class)]
    private PlayerInfo $playerInfo;

    #[ORM\ManyToOne(targetEntity: ClosedDaedalus::class, inversedBy: 'players')]
    private ClosedDaedalus $daedalus;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $endCause = EndCauseEnum::NO_INFIRMERIE;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dayDeath = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleDeath = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $likes = 0;

    #[ORM\Column(type: 'boolean', nullable: false)]
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

    public function getDaedalusLanguage(): string
    {
        return $this->getClosedDaedalus()->getLanguage();
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
        ++$this->likes;

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

    public function getLogName(): string
    {
        return $this->getPlayerInfo()->getCharacterConfig()->getCharacterName();
    }

    public function isAlive(): bool
    {
        return $this->playerInfo->isAlive();
    }

    public function getCharacterKey(): string
    {
        return $this->playerInfo->getCharacterConfig()->getCharacterName();
    }

    public function getUserId(): string
    {
        return $this->playerInfo->getUser()->getUserId();
    }

    public function getUsername(): string
    {
        return $this->playerInfo->getUser()->getUsername();
    }

    public function getClosedDaedalusId(): int
    {
        return $this->daedalus->getId();
    }

    public function getDaysSurvived(): int
    {
        return $this->dayDeath - 1;
    }
}
