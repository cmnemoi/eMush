<?php

namespace Mush\Daedalus\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Enum\EndCauseEnum;

#[ORM\Entity(repositoryClass: DaedalusRepository::class)]
#[ORM\Table(name: 'daedalus_closed')]
class ClosedDaedalus
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: ClosedPlayer::class)]
    private Collection $players;

    #[ORM\ManyToOne(targetEntity: GameConfig::class)]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus = GameStatusEnum::FINISHED;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $endCause = EndCauseEnum::DAEDALUS_DESTROYED;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $endDay;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $endCycle;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?DateTime $filledAt;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?DateTime $finishedAt;

    public function __construct(Daedalus $daedalus)
    {
        $this->endCycle = $daedalus->getCycle();
        $this->endDay = $daedalus->getDay();
        $this->gameConfig = $daedalus->getGameConfig();
        $this->filledAt = $daedalus->getFilledAt();
        $this->finishedAt = $daedalus->getFinishedAt();

        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayers(): ArrayCollection
    {
        return new PlayerCollection($this->players->toArray());
    }

    public function addPlayer(ClosedPlayer $player): static
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);

            $player->setClosedDaedalus($this);
        }

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): static
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getEndCycle(): int
    {
        return $this->endCycle;
    }

    public function getEndDay(): int
    {
        return $this->endDay;
    }

    public function getFilledAt(): ?DateTime
    {
        return $this->filledAt;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setGameStatus(string $gameStatus): static
    {
        $this->gameStatus = $gameStatus;

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
}
