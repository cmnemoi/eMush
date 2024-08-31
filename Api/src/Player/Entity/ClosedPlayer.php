<?php

namespace Mush\Player\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\User\Entity\User;

#[ORM\Entity]
class ClosedPlayer implements SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'closedPlayer', targetEntity: PlayerInfo::class)]
    private PlayerInfo $playerInfo;

    #[ORM\ManyToOne(targetEntity: ClosedDaedalus::class, inversedBy: 'players')]
    private ClosedDaedalus $closedDaedalus;

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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $finishedAt = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $messageIsHidden = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $messageIsEdited = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isAlphaMush = false;

    public function getId(): int
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
        return $this->closedDaedalus;
    }

    public function setClosedDaedalus(ClosedDaedalus $closedDaedalus): self
    {
        $this->closedDaedalus = $closedDaedalus;

        return $this;
    }

    public function getLanguage(): string
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

    public function isHuman(): bool
    {
        return $this->isMush() === false;
    }

    // getter for API Platform serialization
    public function getIsMush(): bool
    {
        return $this->isMush;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function hideMessage(): static
    {
        $this->messageIsHidden = true;

        return $this;
    }

    public function messageIsHidden(): bool
    {
        return $this->messageIsHidden;
    }

    public function editMessage(string $newMessage): static
    {
        $this->setMessage($newMessage);
        $this->messageIsEdited = true;

        return $this;
    }

    public function messageIsEdited(): bool
    {
        return $this->messageIsEdited;
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
        return $this->closedDaedalus->getId();
    }

    public function getUser(): User
    {
        return $this->playerInfo->getUser();
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getCycle(): int
    {
        return $this->getCycleDeath();
    }

    public function getDay(): int
    {
        return $this->getDayDeath();
    }

    public function isAlphaMush(): bool
    {
        return $this->isAlphaMush;
    }

    public function flagAsAlphaMush(): static
    {
        $this->isAlphaMush = true;

        return $this;
    }
}
