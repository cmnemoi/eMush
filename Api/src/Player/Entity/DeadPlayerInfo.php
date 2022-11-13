<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Enum\EndCauseEnum;
use Mush\User\Entity\User;

#[ORM\Entity]
class DeadPlayerInfo
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $character;

    #[ORM\ManyToOne(targetEntity: ClosedDaedalus::class, inversedBy: 'players')]
    private ClosedDaedalus $daedalus;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $endCause = EndCauseEnum::NO_INFIRMERY;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dayDeath;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleDeath;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $likes = [];

    public function __construct(Player $player)
    {
        $this->user = $player->getUser();
        $this->character = $player->getCharacterConfig()->getName();
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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLikes(): array
    {
        return $this->likes;
    }

    public function addLike(DeadPlayerInfo $like): static
    {
        $this->likes[] = $like;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCharacter(): string
    {
        return $this->character;
    }
}
