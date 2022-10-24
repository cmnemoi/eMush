<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Enum\EndCauseEnum;

#[ORM\Entity]
class DeadPlayerInfo
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Player::class)]
    private Player $player;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $endStatus = EndCauseEnum::NO_INFIRMERY;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dayDeath;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleDeath;

    #[ORM\Column(type: 'array', nullable: false)]
    private ?array $likes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): static
    {
        $this->player = $player;

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

    public function setDayDeath(int $dayDeath): static
    {
        $this->dayDeath = $dayDeath;

        return $this;
    }

    public function getCycleDeath(): int
    {
        return $this->cycleDeath;
    }

    public function setCycleDeath(int $cycleDeath): static
    {
        $this->cycleDeath = $cycleDeath;

        return $this;
    }

    public function getLikes(): ?array
    {
        return $this->likes;
    }

    public function setLikes(array $likes): static
    {
        $this->likes = $likes;

        return $this;
    }

    public function getEndStatus(): string
    {
        return $this->endStatus;
    }

    public function setEndStatus(string $endStatus): static
    {
        $this->endStatus = $endStatus;

        return $this;
    }
}
